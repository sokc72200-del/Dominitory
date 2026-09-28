import { createRouter, createWebHistory } from "vue-router";

const routes = [
  { path: "/signin", component: () => import("@/views/SignIn.vue") },
  { path: "/signup", component: () => import("@/views/SignUp.vue") },
  {
    path: "/dashboard",
    component: () => import("@/views/UserDashboard.vue"),
    meta: { requiresAuth: true, role: "user" },
  },
  {
    path: "/admin",
    component: () => import("@/views/AdminDashboard.vue"),
    meta: { requiresAuth: true, role: "admin" },
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.beforeEach((to) => {
  const token = localStorage.getItem("dominitory_token");
  let user = null;
  try {
    user = JSON.parse(localStorage.getItem("dominitory_user") || "null");
  } catch {
    user = null;
  }

  const isAuthenticated = !!token && !!user;

  // Not logged in, trying to hit a protected route
  if (to.meta.requiresAuth && !isAuthenticated) {
    return "/signin";
  }

  // Logged in, but wrong role for this route
  if (to.meta.role && user?.role !== to.meta.role) {
    return user?.role === "admin" ? "/admin" : "/dashboard";
  }

  // Logged in, trying to visit /signin or /signup again
  if ((to.path === "/signin" || to.path === "/signup") && isAuthenticated) {
    return user?.role === "admin" ? "/admin" : "/dashboard";
  }
});

export default router;
