<template>
  <div class="col-12 col-sm-6 col-md-4 col-lg-3 col-xl-2 mb-5">
    <div class="card h-100 mx-0">
      <div class="card-header">
        <div class="row justfy-content-center">
          <div class="col-auto">
            <img :src="user.IMG" alt="" class="img-thumbnail" />
          </div>
          <div class="col">
            <div class="container-fluid">
              <a
                v-bind:href="'https://twitter.com/' + user.USER_SCREEN_NAME"
                target="_blank"
              >
                <div class="row">
                  <h6 class="card-title">{{ user.USER_NAME }}</h6>
                </div>
                <div class="row">
                  <small class="card-subtitle"
                    >@{{ user.USER_SCREEN_NAME }}</small
                  >
                  <small>{{ user.ID }}</small>
                </div>
              </a>
            </div>
          </div>
        </div>
      </div>
      <div class="card-body">
        <p class="card-text">
          {{ user.CONTENT }}
        </p>
      </div>
      <div class="card-footer">
        <div class="container-fluid">
          <div class="row align-items-center">
            <small class="col">{{ user.CREATED_AT }}</small>
            <button
              class="btn btn-primary btn-sm col-auto"
              v-if="showButton && user.IS_FOLLOWING !== undefined"
              v-on:click="follow(user.ID)"
              v-bind:disabled="user.IS_FOLLOWING"
            >
              {{ user.IS_FOLLOWING ? "フォロー済" : "フォロー" }}
            </button>
          </div>
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
    if (this.user.IS_FOLLOWING !== false) return; //フォロー済なら何もしない
    const req = new XMLHttpRequest();
    req.open("GET", "../api/users/follow/" + id);
    req.send(null);
    req.onloadend = () => {
      const RESPONCE_TEXT = JSON.parse(req.responseText);
      console.log(RESPONCE_TEXT);
      if (RESPONCE_TEXT.responce.errors == undefined)
        this.user.IS_FOLLOWING = true;
      else alert("フォローに失敗しました");
    };
  }
}
</script>
