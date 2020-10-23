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
  <div class="container d-flex justify-content-around mb-5">
    <div class="card col-5">
      <div class="card-body">
        <h4 class="card-title">アカウント名</h4>
        <h6 class="card-subtitle">アカウントID</h6>
        <p class="card-text">投稿内容</p>
      </div>
    </div>
    <div class="card col-5">
      <div class="card-body">
        <h4 class="card-title">アカウント名</h4>
        <h6 class="card-subtitle">アカウントID</h6>
        <p class="card-text">投稿内容</p>
      </div>
    </div>
  </div>
</template>
