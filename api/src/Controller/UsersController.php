<?php
declare(strict_types=1);

        
namespace App\Controller;

use Cake\Database\Expression\QueryExpression;
use Cake\ORM\Query;

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
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        require_once(ROOT . DS. 'src' . DS  . 'Controller' .DS  . 'secret.php');
        // require_once './secret.php';
        $request = $this->request;
        $from=$request->getQuery('from', '1990');
        $to=$request->getQuery('to', '9999');
        // $users = $this->paginate($this->Users->find('all', ['condition'=>['and'=>['created_at >='=>$from.'-01-01 00:00:00','created_at <='=>$to.'-12-31 23:59:59']],'order' => ['created_at'=>'desc'],'count'=>100]))->toArray();
        // $users = $this->Users->find('all', ['condition'=>['and'=>['created_at >='=>'1990-01-01 00:00:00','created_at <='=>$to.'1990-12-31 23:59:59']],'order' => ['created_at'=>'desc'],'count'=>100])
        $users = $this->Users->find('all', ['order' => ['created_at'=>'desc'],'count'=>100])
        ->where(function (QueryExpression $exp, Query $q) use ($from,$to) {
            return $exp->between('created_at', $from.'-01-01 00:00:00', $to.'-12-31 23:59:59');
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
        //OAuthトークンとシークレットも使って TwitterOAuth をインスタンス化
        $connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, ACCESS_TOKEN, ACCESS_TOKEN_SECRET);
        //ユーザ情報を100件取得

        session_start();

        //ログイン中か確認し、そうであればscreen_nameを取得
        $screen_name = $this->getAuthUserScreenName($connection);
        $is_logged_in = !array_key_exists('error', $screen_name);
        $following_ids=[];

        //ログインしている場合はフォローしているアカウントのID一覧を取得
        if ($is_logged_in) {
            $following_ids=$connection->get('friends/ids', ['screen_name'=>$screen_name['screen_name'],'count'=>5000,'stringify_ids'=>true]);
            if (is_object($following_ids)&&property_exists($following_ids, 'errors')) {
                $json = json_encode(['users'=>[],'error'=>$following_ids]);
                $this->set(compact('json'));
                $this->viewBuilder()->setLayout('ajax');
                return;
            }
        }
            
        // $user_accounts=$connection->get('users/lookup', ['user_id'=>"783214,782627782488580100",'include_entities'=>false]);
        if (is_object($user_accounts)&&property_exists($user_accounts, 'errors')) {
            $json = json_encode(['users'=>array_values($users_dict),'error'=>$user_accounts->errors]);
            $this->set(compact('json'));
            $this->viewBuilder()->setLayout('ajax');
            return;
        }

        $user_accounts=$connection->get('friends/ids', ['user_id'=>implode(',', array_keys($users_dict))]);

        foreach ($user_accounts as $user) {
            $users_dict[$user->id_str]['name']=$user->name;
            $users_dict[$user->id_str]['screen_name']=$user->screen_name;
            $users_dict[$user->id_str]['img_url']=$user->profile_image_url_https;
            if ($is_logged_in) {
                $users_dict[$user->id_str]['is_following']=in_array($user->id_str, $following_ids);
            }
        }
        //エラー処理（まだやってない）
        // if(array_key_exists("errors",$user_accounts))

        // array_push($users, ['res'=>implode(',', array_keys($users_dict))], ['res'=>$user_accounts]);
            
        $json = json_encode(['users'=>array_values($users_dict),'request'=>$request->getQueryParams()]);
        $this->set(compact('json'));
        $this->viewBuilder()->setLayout('ajax');
    }

    public function auth()
    {
        //事前にセッションを使ってトークン取得済である必要があります
        session_start();
        if (!array_key_exists('access_token', $_SESSION)) {
            return ['screen_name'=>null,'error'=>'user not logged in'];
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
