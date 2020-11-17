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
            btn: true,
            'col-auto': true,
            'mx-2': true,
            'btn-primary': !isLoggedIn,
            'btn-secondary': isLoggedIn,
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
import axios from "axios";

@Options({
  components: {
    UserCard,
  },
})
export default class Index extends Vue {
  private users: Array<User> = [];
  private isLoggedIn = false;
  mounted() {
    axios.get("../api/users/?include=true").then((res) => {
      const json = JSON.parse(res.data);
      console.log(json);
      this.users = json.users.map((user: UserResponce) => {
        return {
          ID: user.id,
          USER_NAME: user.name,
          USER_SCREEN_NAME: user.screen_name,
          IMG: user.img_url,
          CONTENT: user.content,
          CREATED_AT: user.created_at,
        };
      });
    });

    axios.get("../api/users/auth/").then((res) => {
      const json = JSON.parse(res.data);
      console.log(json);
      if (json.screen_name !== null) this.isLoggedIn = true;
    });
  }

  redirect() {
    window.location.href = "../api/users/login/";
  }
}
</script>
