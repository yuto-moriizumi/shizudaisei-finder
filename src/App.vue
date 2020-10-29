<template>
  <div class="container-fluid">
    <router-link to="/">
      <h1 class="text-center">Shizudaisei Finder!</h1>
    </router-link>
  </div>
  <router-view />
  <footer class="container-fluid text-center bg-dark text-white">
    <a href="https://github.com/yuto-moriizumi/shizudaisei-finder"
      ><img src="./assets/github.png"
    /></a>
    <address>
      作者の<a href="https://twitter.com/YutoMoriizumi">Twitter</a>
    </address>
    <small>©2020 Yuto Moriizumi</small>
  </footer>
</template>

<style>
#app {
  font-family: Avenir, Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}

#nav {
  padding: 30px;
}

#nav a {
  font-weight: bold;
  color: #2c3e50;
}

#nav a.router-link-exact-active {
  color: #42b983;
}
</style>
