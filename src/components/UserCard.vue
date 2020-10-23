<template>
  <div class="card">
    <div class="card-header">
      <div class="row">
        <img src="../assets/github.png" alt="" class="col-auto" />
        <div class="col-auto">
          <div class="container">
            <h4 class="card-title row">{{ user.USER_NAME }}</h4>
            <h6 class="card-subtitle row">@{{ user.USER_ID }}</h6>
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
          <button class="btn btn-primary col-auto">フォロー</button>
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
    user: {}
  }
})
export default class UserCard extends Vue {
  private user!: User;
}
</script>
