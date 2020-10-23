<template>
  <div class="jumbotron text-center">
    <h1 class="display-3">仲間を、見つけよう！</h1>
    <p class="lead mb-5">
      Shizudai Finderは、静大生のTwitterユーザを簡単に見つけられるWEBアプリです
    </p>
    <router-link to="/find" class="btn btn-primary"
      >Twitterでログイン</router-link
    >
  </div>
  <div class="container">
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
import { User } from "@/components/User.ts";

@Options({
  components: {
    UserCard
  }
})
export default class Find extends Vue {
  private users: Array<User> = [];
  mounted() {
    setTimeout(() => {
      this.users = [
        {
          USER_NAME: "田中太郎",
          USER_ID: "tanaka999",
          IMG:
            "https://pbs.twimg.com/profile_images/1287396439522332672/UYs39jOS_400x400.jpg",
          CONTENT:
            "一日のエネルギーの95%を使ったのでもう動けはしないけれども、デザインを続けていこう　道中実装についても考えていた",
          CREATED_AT: "2020-02-02"
        },{
          USER_NAME: "田中太郎",
          USER_ID: "tanaka999",
          IMG:
            "https://pbs.twimg.com/profile_images/1287396439522332672/UYs39jOS_400x400.jpg",
          CONTENT:
            "一日のエネルギーの95%を使ったのでもう動けはしないけれども、デザインを続けていこう　道中実装についても考えていた",
          CREATED_AT: "2020-02-02"
        },{
          USER_NAME: "田中太郎",
          USER_ID: "tanaka999",
          IMG:
            "https://pbs.twimg.com/profile_images/1287396439522332672/UYs39jOS_400x400.jpg",
          CONTENT:
            "一日のエネルギーの95%を使ったのでもう動けはしないけれども、デザインを続けていこう　道中実装についても考えていた",
          CREATED_AT: "2020-02-02"
        }
      ];
    }, 500);
  }
}
</script>
