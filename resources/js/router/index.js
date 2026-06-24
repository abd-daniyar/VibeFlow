import { createRouter, createWebHistory } from 'vue-router'

const routes = [
  {
    path: '/',
    name: 'Boards',
    component: () => import('../pages/Boards.vue'),
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

export default router
