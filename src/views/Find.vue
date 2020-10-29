<template>
  <div class="container-fluid">
    <div class="alert alert-primary p-0">
      <div class="row align-items-center">
        <p class="mx-auto mb-0">
          <img :src="profileImgUrl" alt="" />You logged in as {{ name }}
        </p>
      </div>
    </div>
  </div>
  <div class="container">
    <h1>検索条件</h1>
    <form class="mx-5 mb-3" @submit="find">
      <div class="row align-items-baseline">
        <select name="from" class="custom-select col" v-model="from">
          <option value="1990">指定しない</option>
          <option>2017</option>
          <option>2018</option>
          <option>2019</option>
        </select>
        <p class="col-auto">から</p>
        <select name="to" class="custom-select col" v-model="to">
          <option value="9999">指定しない</option>
          <option>2017</option>
          <option>2018</option>
          <option>2019</option>
        </select>
        <p class="col-auto">まで</p>
        <div class="custom-control custom-checkbox col-auto">
          <input
            id="include"
            type="checkbox"
            class="custom-control-input"
            v-model="include"
          />
          <label class="custom-control-label" for="include"
            >フォローしている人を含む</label
          >
        </div>
        <button class="btn btn-primary col-auto">
          検索
        </button>
      </div>
    </form>
    <div class="row align-items-center mb-2">
      <h1 class="col">検索結果</h1>
      <div class="col-auto">
        <button class="btn btn-primary btn-lg" v-on:click="follow">
          一括フォロー
        </button>
      </div>
    </div>
    <div class="alert alert-success" v-if="showFollowAlert">
      {{ followedCount }}人フォローしました
      <button
        type="button"
        class="close"
        aria-label="Close"
        v-on:click="showFollowAlert = false"
      >
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
    <div class="container" v-if="notFound">
      <div class="alert alert-danger row">
        検索結果が見つかりませんでした
      </div>
    </div>
  </div>
  <div class="container-fluid">
    <div class="card-deck">
      <UserCard
        v-for="user in users"
        v-bind:key="user.USER_ID"
        v-bind:user="user"
        :showButton="true"
      />
    </div>
  </div>
</template>

<script lang="ts">
import { Options, Vue } from "vue-class-component";
import UserCard from "@/components/UserCard.vue";
import { User, UserResponce } from "@/components/User";

@Options({
  components: {
    UserCard
  }
})
export default class Find extends Vue {
  name = "";
  profileImgUrl = "./img/github.png";
  from = 1990;
  to = 9999;
  include = false;
  users = Array<User>();
  showFollowAlert = false;
  followedCount = 0;
  notFound = false;
  mounted() {
    const req = new XMLHttpRequest();
    req.open("GET", "../api/users/auth/");
    req.send(null);
    req.onloadend = () => {
      const RESPONCE_TEXT = JSON.parse(req.responseText);
      console.log(RESPONCE_TEXT);
      this.name = RESPONCE_TEXT.name;
      if (this.name === null) window.location.href = "../twitter/auth.php"; //未認証であれば認証にリダイレクト
      this.profileImgUrl = RESPONCE_TEXT.img_url;
    };
  }

  find(event: any) {
    event.preventDefault();
    console.log(this.from, this.to, this.include);
    const req = new XMLHttpRequest();
    req.open(
      "GET",
      "../api/users/?from=" +
        this.from +
        "&to=" +
        this.to +
        "&include=" +
        this.include
    );
    req.send(null);
    req.onloadend = () => {
      const RESPONCE_TEXT = JSON.parse(req.responseText);
      console.log(RESPONCE_TEXT);

      this.users = RESPONCE_TEXT.users.map((user: UserResponce) => {
        return {
          ID: user.id,
          USER_NAME: user.name,
          USER_SCREEN_NAME: user.screen_name,
          IMG: user.img_url,
          CONTENT: user.content,
          CREATED_AT: user.created_at,
          IS_FOLLOWING: user.is_following
        };
      });
      this.notFound = this.users.length == 0;
    };
  }

  follow() {
    this.followedCount = 0;
    this.users.forEach(user => {
      if (user.IS_FOLLOWING !== false) return; //フォロー済か対象ユーザが見つからなかったなら何もしない
      const req = new XMLHttpRequest();
      req.open("GET", "../api/users/follow/" + user.ID);
      req.send(null);
      req.onloadend = () => {
        const RESPONCE_TEXT = JSON.parse(req.responseText);
        console.log(RESPONCE_TEXT);
        this.followedCount += 1;
      };
    });
    this.showFollowAlert = true;
  }
}
</script>
