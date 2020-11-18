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
  <div class="container-fluid">
    <div class="row">
      <div class="col-xl-1"></div>
      <div class="col">
        <h1 class="text-center text-sm-left">検索条件</h1>
        <form class="mx-5 mb-3" @submit="find">
          <div class="row align-items-baseline">
            <input type="date" class="col-auto from-control" v-model="from" />
            <p class="col-auto">から</p>
            <input type="date" class="col-auto from-control" v-model="to" />
            <p class="col-auto">まで</p>
            <div class="custom-control custom-checkbox col-auto">
              <input
                id="include_follow"
                type="checkbox"
                class="custom-control-input"
                v-model="include_follow"
              />
              <label class="custom-control-label" for="include_follow"
                >フォローしている人を含む</label
              >
            </div>
          </div>
          <fieldset class="mb-3">
            <div class="row align-items-baseline">
              <legend class="col-form-label col-12 col-lg-1">学部</legend>
              <div
                class="custom-control custom-checkbox col-12 col-sm-6 col-md-4 col-lg-auto mr-xl-4"
                v-for="(faculty, key) in faculties"
                :key="key"
              >
                <input
                  :id="key"
                  type="checkbox"
                  class="custom-control-input"
                  v-model="faculties[key].include"
                />
                <label class="custom-control-label" :for="key">{{
                  faculty.name
                }}</label>
              </div>
            </div>
            <small class="text-muted"
              >チェックを外すとその学部と思われるユーザを除外します（正確ではありません）</small
            >
          </fieldset>
          <div class="text-center">
            <button class="btn btn-primary col-8">検索</button>
          </div>
        </form>
        <div class="row align-items-center mb-2">
          <h1 class="col-12 col-sm-6 text-center text-sm-left">検索結果</h1>
          <button
            class="btn btn-primary btn-lg col-8 col-sm-auto text-center text-sm-left mx-auto"
            v-on:click="follow"
          >
            一括フォロー
          </button>
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
      <div class="col-xl-1"></div>
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
import axios from "axios";
import qs from "qs";

@Options({
  components: {
    UserCard,
  },
})
export default class Find extends Vue {
  name = "";
  profileImgUrl = "./img/github.png";
  from = "2020-01-01";
  to = "2020-12-31";
  include_follow = false;
  users = Array<User>();
  showFollowAlert = false;
  followedCount = 0;
  notFound = false;
  faculties = {
    hss: { name: "人文社会科学部", include: true },
    edu: { name: "教育学部", include: true },
    sci: { name: "理学部", include: true },
    agr: { name: "農学部", include: true },
    inf: { name: "情報学部", include: true },
    eng: { name: "工学部", include: true },
  };

  mounted() {
    axios.get("../api/users/auth/").then((res) => {
      this.name = res.data.name;
      if (this.name === null) window.location.href = "../twitter/auth.php"; //未認証であれば認証にリダイレクト
      this.profileImgUrl = res.data.img_url;
    });
  }

  find(event: any) {
    event.preventDefault();
    this.showFollowAlert = false;

    const query =
      "../api/users/?" +
      qs.stringify({
        from: this.from,
        to: this.to,
        include_follow: this.include_follow,
        ...Object.fromEntries(
          Object.entries(this.faculties).map((entry) => {
            return [entry[0], entry[1].include];
          })
        ),
      });
    console.log(query);

    axios.get(query).then((res) => {
      this.users = res.data.users.map((user: UserResponce) => {
        return {
          ID: user.id,
          USER_NAME: user.name,
          USER_SCREEN_NAME: user.screen_name,
          IMG: user.img_url,
          CONTENT: user.content,
          CREATED_AT: user.created_at,
          IS_FOLLOWING: user.is_following,
        };
      });
      this.notFound = this.users.length == 0; //検索結果が0件であれば「見つかりませんでした」と表示
    });
  }

  follow() {
    this.followedCount = 0;
    this.users.forEach((user) => {
      if (user.IS_FOLLOWING !== false) return; //フォロー済か対象ユーザが見つからなかったなら何もしない
      const query = "../api/users/follow/" + user.ID;
      console.log(query);
      axios.get(query).then((res) => {
        this.followedCount += 1;
        user.IS_FOLLOWING = true;
      });
    });
    this.showFollowAlert = true;
  }
}
</script>
