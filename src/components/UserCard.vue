<template>
  <div class="card">
    <div class="card-header">
      <div class="row">
        <img :src="user.IMG" alt="" class="col" />
        <div class="col">
          <div class="container">
            <h4 class="card-title row">{{ user.USER_NAME }}</h4>
            <h6 class="card-subtitle row">@{{ user.USER_SCREEN_NAME }}</h6>
          </div>
        </div>
      </div>
    </div>
    <div class="card-body">
      <p class="card-text">
        {{ user.CONTENT }}
      </p>
      <div class="container">
        <div class="row align-items-center">
          <small class="col">{{ user.CREATED_AT }}</small>
          <button
            class="btn btn-primary col-auto"
            v-if="showButton"
            v-on:click="follow(user.ID)"
          >
            フォロー
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Options, Vue } from "vue-class-component";
import { User } from "@/components/User.ts";

@Options({
  props: {
    user: {},
    showButton: false
  }
})
export default class UserCard extends Vue {
  private user!: User;
  private showButton = false;

  follow(id: string) {
    const req = new XMLHttpRequest();
    req.open("GET", "../api/users/follow/" + id);
    req.send(null);
    req.onloadend = () => {
      const RESPONCE_TEXT = JSON.parse(req.responseText);
      console.log(RESPONCE_TEXT);
    };
  }
}
</script>
