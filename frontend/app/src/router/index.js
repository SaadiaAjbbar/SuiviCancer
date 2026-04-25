import { createRouter, createWebHistory } from 'vue-router'
// On importe nos composants (pages)
import Login from '../views/Login.vue'
import CreerHopital from '../views/CreerHopital.vue'
import AdminHopitalDashboard from '../views/AdminHopitalDashboard.vue'
import DashboardInfirmier from '../views/DashboardInfirmier.vue'
import DashboardMedecin from '../views/DashboardMedecin.vue'
import MedecinLayout from '@/views/MedecinLayout.vue'
import DashboardView from '@/views/DashboardView.vue'
import ConsultationsView from '@/views/ConsultationsView.vue'
import QuestionsView from '@/views/QuestionsView.vue'
import PatientLayout from '@/views/PatientLayout.vue'
import DashboardPatient from '@/views/DashboardPatient.vue'


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
    },
    //{
    //   path: '/dashboard-medecin',
    //   name: 'DashboardMedecin',
    //   component: DashboardMedecin
    // } ,
    {
      path: '/medecin',
      component: MedecinLayout,
      meta: { requiresAuth: true },
      children: [
        {
          path: '', // Hada howa l-default (/medecin)
          name: 'medecin-dashboard',
          component: DashboardView
        }, {
          path: 'patients',
          name: 'medecin-patients',
          component: () => import('../views/MesPatients.vue')
        },
        {
          path: 'consultations', // (/medecin/consultations)
          name: 'medecin-consultations',
          component: ConsultationsView
        },
        {
          path: 'questions', // (/medecin/questions)
          name: 'medecin-questions',
          component: QuestionsView
        },
        {
          path: 'responses', // (/medecin/responses)
          name: 'medecin-responses',
          component: () => import('../views/PatientResponses.vue')
        }
      ]
    },
    {
      path: '/patient',
      component: PatientLayout,
      meta: { requiresAuth: true },
      children: [
        {
          path: '', // Hada howa l-default (/medecin)
          name: 'patient-dashboard',
          component: DashboardPatient
        },

        {
          path: 'bilans', // URL: /patient/bilans
          name: 'patient-bilans',
          component: () => import('../views/PatientBilans.vue') // Exemple de chargement différé
        },
        {
          path: 'rendez-vous', // URL: /patient/rendez-vous
          name: 'patient-rdv',
          component: () => import('../views/PatientRDV.vue')
        },
        {
          path: 'traitements', // URL: /patient/traitements
          name: 'patient-traitements',
          component: () => import('../views/PatientTraitements.vue')
        },
        {
          path: 'conseils', // URL: /patient/conseils
          name: 'patient-conseils',
          component: () => import('../views/PatientConseils.vue')
        }, {
          path: 'consultations', // correspond à to="/patient/consultations" dans le layout
          name: 'patient-consultations',
          component: () => import('../views/PatientConsultations.vue')
        }

      ]
    }
  ],
})

export default router
