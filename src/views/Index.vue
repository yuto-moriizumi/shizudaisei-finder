<template>
  <div class="jumbotron text-center">
    <h1 class="display-3">仲間を、見つけよう！</h1>
    <p class="lead mb-5">
      Shizudai Finderは、静大生のTwitterユーザを簡単に見つけられるWEBアプリです
    </p>
    <div class="container">
      <div class="row justify-content-center">
        <button
          v-bind:class="{
            btn,
            'col-auto': true,
            'mx-2': true,
            'btn-primary': !isLoggedIn,
            'btn-secondary': isLoggedIn
          }"
          v-on:click="redirect"
        >
          Twitterでログイン
        </button>
        <router-link
          to="/find"
          class="btn btn-primary col-auto mx-2"
          v-if="isLoggedIn"
        >
          静大生を探す
        </router-link>
      </div>
    </div>
  </div>
  <div class="container-fluid">
    <div class="card-deck mb-5">
      <UserCard
        v-for="user in users"
        v-bind:key="user.USER_ID"
        v-bind:user="user"
      />
    </div>
  </div>
</template>

<script lang="ts">
import { Options, Vue } from "vue-class-component";
import UserCard from "@/components/UserCard.vue";
import { User, UserResponce } from "@/components/User.ts";

@Options({
  components: {
    UserCard
  }
})
export default class Index extends Vue {
  private users: Array<User> = [];
  private isLoggedIn = false;
  mounted() {
    console.log("vue mounted2!");

    const req = new XMLHttpRequest();
    req.open("GET", "../api/users/");
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
          CREATED_AT: user.created_at
        };
      });
    };

    const req2 = new XMLHttpRequest();
    req2.open("GET", "../api/users/auth/");
    req2.send(null);
    req2.onloadend = () => {
      const RESPONCE_TEXT = JSON.parse(req2.responseText);
      console.log(RESPONCE_TEXT);
      if (RESPONCE_TEXT.name !== null) this.isLoggedIn = true;
    };
  }

  redirect() {
    window.location.href = "../api/users/login/";
  }
}
</script>
