import { createRouter, createWebHistory } from "vue-router";
import routes from "./routes";

import { authServices } from "@/services/auth-services";

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
  scrollBehavior(to, from, savedPosition) {
    return savedPosition || { left: 0, top: 0 };
  },
});

// Middleware global for authentication and setting pages
router.beforeEach((to, from, next) => {
  const requiresAuth = to.matched.some((record) => record.meta.requiresAuth);
  const isAuthenticated = authServices.isAuthenticated();

  if (requiresAuth && !isAuthenticated) {
    next("/");
  } else if (to.path === "/" && isAuthenticated) {
    next("/");
  } else {
    next();
  }

  document.title = `${to.meta.title} | Gmedia Issues Tracker`;
});

export default router;
