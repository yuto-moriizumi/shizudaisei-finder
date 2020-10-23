<template>
  <div class="container-fluid">
    <div class="alert alert-primary p-0">
      <div class="row align-items-center">
        <p class="mx-auto mb-0">
          <img src="../assets/github.png" alt="" />You logged in as NAME
        </p>
      </div>
    </div>
  </div>
  <div class="container">
    <h1>検索条件</h1>
    <form class="mx-5 mb-3">
      <div class="row align-items-baseline">
        <select name="from" class="custom-select col">
          <option>2017</option>
          <option>2018</option>
          <option>2019</option>
        </select>
        <p class="col-auto">から</p>
        <select name="to" class="custom-select col">
          <option>2017</option>
          <option>2018</option>
          <option>2019</option>
        </select>
        <p class="col-auto">まで</p>
        <div class="custom-control custom-checkbox col-auto">
          <input id="include" type="checkbox" class="custom-control-input" />
          <label class="custom-control-label" for="include"
            >フォローしている人を含む</label
          >
        </div>
        <button class="btn btn-primary col-auto">検索</button>
      </div>
    </form>
    <div class="row align-items-center mb-2">
      <h1 class="col">検索結果</h1>
      <div class="col-auto">
        <button class="btn btn-primary btn-lg">一括フォロー</button>
      </div>
    </div>
    <div class="card-deck mb-5">
      <UserCard />
      <UserCard />
    </div>
  </div>
</template>

<script lang="ts">
import { Options, Vue } from "vue-class-component";
import UserCard from "@/components/UserCard.vue";

@Options({
  components: {
    UserCard
  }
})
export default class Find extends Vue {}
</script>
