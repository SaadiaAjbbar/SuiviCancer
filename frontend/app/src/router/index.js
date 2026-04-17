import { createRouter, createWebHistory } from 'vue-router'
// On importe nos composants (pages)
import Login from '../views/Login.vue'
import CreerHopital from '../views/CreerHopital.vue'
import AdminHopitalDashboard from '../views/AdminHopitalDashboard.vue'
import DashboardInfirmier from '../views/DashboardInfirmier.vue'
const router = createRouter({
  // createWebHistory permet d'avoir des URLs propres comme /login au lieu de /#/login
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [

    {
      path: '/login',
      name: 'login',
      component: Login, // C'est ici qu'on lie l'URL au composant
    },
    {
      path: '/creer-hopital',
      name: 'creer-hopital',
      component: CreerHopital
    }, {
      path: '/admin-hopital-dashboard',
      name: 'AdminHopitalDashboard',
      component: AdminHopitalDashboard
    }, {
      path: '/dashboard-infirmier',
      name: 'DashboardInfirmier',
      component: DashboardInfirmier
    }
  ],
})

export default router
