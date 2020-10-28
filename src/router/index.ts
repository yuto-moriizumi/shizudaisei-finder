import { createRouter,  createWebHashHistory ,RouteRecordRaw } from "vue-router";
import Index from "../views/Index.vue";

const routes: Array<RouteRecordRaw> = [
  {
    path: "/",
    name: "Index",
    component: Index
  },
  {
    path: "/find",
    name: "Find",
    component: () =>
      import(/* webpackChunkName: "about" */ "../views/Find.vue")
  }
];

const router = createRouter({
  history: createWebHashHistory(),
  routes
});

export default router;
