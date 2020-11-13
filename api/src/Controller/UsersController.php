<?php
declare(strict_types=1);

        
namespace App\Controller;

use Cake\Database\Expression\QueryExpression;
use Cake\ORM\Query;
use Cake\I18n\Time;

use Abraham\TwitterOAuth\TwitterOAuth;

/**
 * Users Controller
 *
 * @property \App\Model\Table\UsersTable $Users
 * @method \App\Model\Entity\User[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class UsersController extends AppController
{
    /**
     * Index method
     * 登録済みユーザ一覧をjsonで出力します
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        require_once(ROOT . DS. 'src' . DS  . 'Controller' .DS  . 'secret.php');
        // require_once './secret.php';
        $request = $this->request;
        $from=$request->getQuery('from', '1990-01-01');
        $to=$request->getQuery('to', '2200-12-31');
        $include=$request->getQuery('include', false);
        $include=$include=="true"?true:false;
        // $users = $this->paginate($this->Users->find('all', ['condition'=>['and'=>['created_at >='=>$from.'-01-01 00:00:00','created_at <='=>$to.'-12-31 23:59:59']],'order' => ['created_at'=>'desc'],'limit'=>100]))->toArray();
        // $users = $this->Users->find('all', ['condition'=>['and'=>['created_at >='=>'1990-01-01 00:00:00','created_at <='=>$to.'1990-12-31 23:59:59']],'order' => ['created_at'=>'desc'],'limit'=>100])
        $users = $this->Users->find('all', ['order' => ['created_at'=>'desc']])
        ->where(function (QueryExpression $exp, Query $q) use ($from,$to) {
            return $exp->between('created_at', $from.' 00:00:00', $to.' 23:59:59');
        })->toArray();

        if (count($users)===0) {
            $json = json_encode(['users'=>[],'request'=>$request->getQueryParams()]);
            $this->set(compact('json'));
            $this->viewBuilder()->setLayout('ajax');
            return;
        }

        $users_dict=[];
        foreach ($users as $user) {
            $users_dict[$user["id"]]=$user;
        }

        //ログイン済みであるか確認
        session_start();
        $is_logged_in = false;
        if (array_key_exists('access_token', $_SESSION)) {
            $is_logged_in=true;
        }

        //TwitterAPIと接続
        require_once(ROOT . DS. 'src' . DS  . 'Controller' .DS  . 'secret.php');
        $access_token = $is_logged_in?$_SESSION['access_token']:['oauth_token'=>ACCESS_TOKEN,'oauth_token_secret'=>ACCESS_TOKEN_SECRET];
        $connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $access_token['oauth_token'], $access_token['oauth_token_secret']);
        
        $following_ids=[];
        //ログインしている場合はフォローしているアカウントのID一覧を取得
        if ($is_logged_in) {
            $following_ids=$connection->get('friends/ids', ['screen_name'=>$user->screen_name,'count'=>5000,'stringify_ids'=>true]);
            if (is_object($following_ids)&&property_exists($following_ids, 'errors')) {
                $json = json_encode(['users'=>[],'error'=>$following_ids]);
                $this->set(compact('json'));
                $this->viewBuilder()->setLayout('ajax');
                return;
            }
            $following_ids=$following_ids->ids;
        }

        //includeがfalseであれば、フォロー中のユーザを除外します
        if (!$include) {
            $users_dict=array_filter($users_dict, function ($user_id) use ($following_ids) {
                return !in_array($user_id, $following_ids); //フォロー中のユーザでない
            }, ARRAY_FILTER_USE_KEY);
        }

        //users/lookupは100件までしか受け付けないので絞る
        $users_dict=array_slice($users_dict, 0, 100, true);
            
        $user_accounts=$connection->get('users/lookup', ['user_id'=>implode(',', array_keys($users_dict)),'include_entities'=>false]);
        // $user_accounts=$connection->get('users/lookup', ['user_id'=>implode(',', array_slice(array_keys($users_dict), 0, 100)),'include_entities'=>false]);
        if (is_object($user_accounts)&&property_exists($user_accounts, 'errors')) {
            $json = json_encode(['users'=>array_values($users_dict),'error'=>$user_accounts->errors]);
            // $json = json_encode(['user_accounts'=>$user_accounts]);
            $this->set(compact('json'));
            $this->viewBuilder()->setLayout('ajax');
            return;
        }

        //取得した情報を追加
        $found_user_ids=[];
        foreach ($user_accounts as $user) {
            array_push($found_user_ids, $user->id_str);
            $users_dict[$user->id_str]['name']=$user->name;
            $users_dict[$user->id_str]['screen_name']=$user->screen_name;
            $users_dict[$user->id_str]['img_url']=$user->profile_image_url_https;
            $date=$users_dict[$user->id_str]['created_at'];
            $date = Time::parse($date);
            $users_dict[$user->id_str]['created_at']=$date->i18nFormat('yyyy-MM-dd');
            if ($is_logged_in) {
                $users_dict[$user->id_str]['is_following']=in_array($user->id_str, $following_ids);
            }
        }

        $cc=0;
        //Twitterを退会したユーザをDBから削除
        foreach ($users_dict as $user_object) {
            if (in_array($user_object['id'], $found_user_ids)) {
                continue;
            } //img_urlが存在すれば、Twitterから正しく取得できたということ
            $cc+=1;
            // array_push($deeee2, [$user_object['id'].' doesnt has img_url',$user_object['img_url']]);
            try {
                $user_entity = $this->Users->get($user_object->id);
                $this->Users->delete($user_entity);
            } catch (\Throwable $th) {
            }
        }
            
        $json = json_encode(['users'=>array_values($users_dict),'request'=>$request->getQueryParams(),'deleted_count'=>$cc]);
        $this->set(compact('json'));
        $this->viewBuilder()->setLayout('ajax');
    }

    public function cron()
    {
        //定期的に呼び出され、#春から静大を使ったツイートを取得します
        require_once(ROOT . DS. 'src' . DS  . 'Controller' .DS  . 'secret.php');

        $connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, ACCESS_TOKEN, ACCESS_TOKEN_SECRET);
        
        $tweets=$connection->get('search/tweets', [ //連想配列としてパース
            'q'=>'#春から静大',
            'count'=>100,
            'result_type'=>'recent',
            'include_entities'=>false
        ]);

        if ($connection->getLastHttpCode() !== 200) {
            $json = json_encode(['errors'=>$tweets->errors]);
            $this->set(compact('json'));
            $this->viewBuilder()->setLayout('ajax');
            $this->viewBuilder()->setTemplate('printr');
            return;
        }

        $users=[];
        //各ツイートから必要な情報を抜き出してDBに保存
        foreach ($tweets->statuses as $tweet) {
            if (array_key_exists('retweeted_status', $tweet)) { //リツイートであれば除外する
                continue;
            }
            $user=$this->Users->newEmptyEntity();
            $user['id']=$tweet->user->id_str;
            $user['tweet_id']=$tweet->id_str;
            $user['content']=$tweet->text;
            $user['created_at']= Time::parse($tweet->created_at);
            $result=$this->Users->save($user)?true:false;
            array_push($users, [$result,$user]);
        }
        
        $json = json_encode(['users'=>$users]);
        $this->set(compact('json'));
        $this->viewBuilder()->setLayout('ajax');
        $this->viewBuilder()->setTemplate('printr');
    }

    public function search($next=null)
    {
        session_start();
        require_once(ROOT . DS. 'src' . DS  . 'Controller' .DS  . 'secret.php');
        $connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, ACCESS_TOKEN, ACCESS_TOKEN_SECRET);
        //
        //tweets/search/30day/tweets
        // $_SESSION['']
        $options=[ //連想配列としてパース
            // "user_id"=>$id,
            // "exclude_replies"=>true,
            // "include_rts"=>false,
            // "count"=>100,
            'query'=>'#春から静大',
            // 'result_type'=>'recent',
            // 'include_entities'=>false
            "fromDate"=>"200612220000",
        ];
        if ($next) {
            $options["next"]=$next;
        } elseif ($_SESSION["next"]) {
            $options["next"]=$_SESSION["next"];
        }
        $tweets=$connection->get('tweets/search/fullarchive/test2', $options);

        if ($connection->getLastHttpCode() !== 200) {
            $json = json_encode(['error'=>$tweets->error]);
            $this->set(compact('json'));
            $this->viewBuilder()->setLayout('ajax');
            $this->viewBuilder()->setTemplate('printr');
            return;
        }

        $users=[];
        //各ツイートから必要な情報を抜き出してDBに保存
        foreach ($tweets->results as $tweet) {
            if (property_exists($tweet, 'retweeted_status')) {
                // if (array_key_exists('retweeted_status', $tweet)) { //リツイートであれば除外する
                continue;
            }
            $user=$this->Users->newEmptyEntity();
            $user['id']=$tweet->user->id_str;
            $user['tweet_id']=$tweet->id_str;
            $user['content']=$tweet->text;
            $user['created_at']= Time::parse($tweet->created_at);
            $result=$this->Users->save($user)?true:false;
            array_push($users, [$result,$user]);
        }

        $json = $tweets;
        $this->set(compact('json'));
        $this->viewBuilder()->setLayout('ajax');
        $this->viewBuilder()->setTemplate('printr');

        $_SESSION['next']=$tweets->next;
    }

    public function test()
    {
        // $users=$this->Users->find('all', ['order' => ['created_at'=>'desc'],'limit'=>100]);
        // require_once(ROOT . DS. 'src' . DS  . 'Controller' .DS  . 'secret.php');
        // $connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, ACCESS_TOKEN, ACCESS_TOKEN_SECRET);
        // $user_accounts=$connection->get('users/lookup', ['user_id'=>implode(',', array_keys($users_dict)),'include_entities'=>false]);

        // $json = json_encode(['users'=>array_values($users_dict),'request'=>$request->getQueryParams(),'d'=>['sc'=>$SC,'sd'=>$SD]]);
        $array=[];
        $array["10"]=10;
        $array["5"]=5;
        $json = json_encode(['array1'=>$array]);
        $this->set(compact('json'));
        $this->viewBuilder()->setLayout('ajax');
        $this->viewBuilder()->setTemplate('printr');
    }

    public function auth()
    {
        //事前にセッションを使ってトークン取得済である必要があります
        session_start();
        if (!array_key_exists('access_token', $_SESSION)) {
            $json= json_encode(['screen_name'=>null,'error'=>'user not logged in','session'=>$_SESSION]);
            $this->set(compact('json'));
            $this->viewBuilder()->setLayout('ajax');
            $this->viewBuilder()->setTemplate('index');
            return;
        }
        $access_token = $_SESSION['access_token'];
        require_once(ROOT . DS. 'src' . DS  . 'Controller' .DS  . 'secret.php');
        $connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $access_token['oauth_token'], $access_token['oauth_token_secret']);

        //ログインユーザを取得
        $user=$connection->get('account/settings', []);

        if (is_object($user)&&property_exists($user, 'error')) {
            $json = json_encode(['screen_name'=>null,'errors'=>$user['error']]);
        } else {
            $user2 = $connection->get('users/lookup', ['screen_name'=>$user->screen_name]);
            if (is_object($user2) && property_exists($user2, 'errors')) {
                $json = json_encode(['screen_name'=>$user->screen_name,'errors'=>$user2->errors]);
            } else {
                $json = json_encode(['name'=>$user2[0]->name,'img_url'=>$user2[0]->profile_image_url_https]);
            }
        }
        
        $this->set(compact('json'));
        $this->viewBuilder()->setLayout('ajax');
        $this->viewBuilder()->setTemplate('index');
    }

    private function getAuthUserScreenName($connection)
    {
        //ログインユーザを取得
        $user=$connection->get('account/settings', []);
        if (property_exists($user, 'error')) {
            return ['screen_name'=>null,'error'=>$user['errors']];
        } else {
            return ['screen_name'=>$user->screen_name];
        }
    }

    public function login()
    {
        require_once(ROOT . DS. 'src' . DS  . 'Controller' .DS  . 'secret.php');
        $CONSUMER_KEY=CONSUMER_KEY;
        $CONSUMER_SECRET=CONSUMER_SECRET;
        $this->set(compact('CONSUMER_KEY', 'CONSUMER_SECRET'));
    }

    public function callback()
    {
        require_once(ROOT . DS. 'src' . DS  . 'Controller' .DS  . 'secret.php');
        $CONSUMER_KEY=CONSUMER_KEY;
        $CONSUMER_SECRET=CONSUMER_SECRET;
        $this->set(compact('CONSUMER_KEY', 'CONSUMER_SECRET'));
    }
        

    public function follow($id)
    {
        require_once(ROOT . DS. 'src' . DS  . 'Controller' .DS  . 'secret.php');
        session_start();
        if (!array_key_exists('access_token', $_SESSION)) {
            $json = json_encode(['error'=>'User not authorized!']);
            $this->set(compact('json'));
            $this->viewBuilder()->setLayout('ajax');
            $this->viewBuilder()->setTemplate('index');
            return;
        }

        $access_token = $_SESSION['access_token'];
        $connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $access_token['oauth_token'], $access_token['oauth_token_secret']);
        $result=$connection->post('friendships/create', ['user_id'=>$id]);
        $json = json_encode(['responce'=>$result]);
        $this->set(compact('json'));
        $this->viewBuilder()->setLayout('ajax');
        $this->viewBuilder()->setTemplate('index');
    }

    public function index2()
    {
        $users = $this->paginate($this->Users->find('all', ['order' => ['created_at'=>'desc']]));
        // $users = $this->paginate($this->Users);
        $this->set(compact('users'));
        $this->viewBuilder()->setLayout('default');
    }

    /**
     * View method
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $user = $this->Users->get($id, [
            'contain' => [],
        ]);

        $this->set(compact('user'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $user = $this->Users->newEmptyEntity();
        if ($this->request->is('post')) {
            $user = $this->Users->patchEntity($user, $this->request->getData());
            if ($this->Users->save($user)) {
                $this->Flash->success(__('The user has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The user could not be saved. Please, try again.'));
        }
        $this->set(compact('user'));
    }

    /**
     * Edit method
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $user = $this->Users->get($id, [
            'contain' => [],
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $user = $this->Users->patchEntity($user, $this->request->getData());
            if ($this->Users->save($user)) {
                $this->Flash->success(__('The user has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The user could not be saved. Please, try again.'));
        }
        $this->set(compact('user'));
    }

    /**
     * Delete method
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $user = $this->Users->get($id);
        if ($this->Users->delete($user)) {
            $this->Flash->success(__('The user has been deleted.'));
        } else {
            $this->Flash->error(__('The user could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
