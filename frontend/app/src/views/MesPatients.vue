<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import AppCard from '@/components/ui/AppCard.vue';

const patients = ref([]);
const loading = ref(true);
const errorMessage = ref('');

onMounted(async () => {
  const token = localStorage.getItem('user_token');
  try {
    const response = await axios.get('http://localhost:8080/api/medecin/my-patients', {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    });
    patients.value = response.data;
  } catch (error) {
    if (error.response?.status === 401) {
      errorMessage.value = "Session expirée. Veuillez vous reconnecter.";
    } else {
      errorMessage.value = "Erreur lors de la récupération des patients.";
    }
  } finally {
    loading.value = false;
  }
});
</script>

<template>
  <div class="space-y-10 animate-in fade-in duration-500 pb-20">
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
      <div class="space-y-2">
        <h1 class="text-4xl font-black text-slate-900 tracking-tight flex items-center gap-4">
          <span class="p-3 bg-primary/10 rounded-2xl text-2xl shadow-sm">👥</span>
          Mes Patients
        </h1>
        <p class="text-slate-500 font-medium text-lg">Suivi et gestion de votre file active de patients.</p>
      </div>
      <div class="flex items-center gap-3 px-6 py-3 bg-white border border-slate-200 rounded-2xl shadow-sm">
        <span class="w-3 h-3 bg-emerald-500 rounded-full animate-pulse"></span>
        <span class="text-sm font-black text-slate-900 uppercase tracking-widest">{{ patients.length }} Patients assignés</span>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="py-32 flex flex-col items-center justify-center bg-white rounded-[3rem] border border-slate-100 shadow-sm">
      <div class="w-16 h-16 border-4 border-primary/10 border-t-primary rounded-full animate-spin"></div>
      <p class="mt-6 text-slate-400 font-black uppercase tracking-widest text-xs">Synchronisation des dossiers...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="errorMessage" class="p-8 bg-red-50 border border-red-100 rounded-3xl flex items-center gap-6 text-red-600 animate-in shake duration-500">
      <div class="w-14 h-14 bg-white rounded-2xl flex items-center justify-center text-3xl shadow-sm">⚠️</div>
      <div>
        <h4 class="font-black uppercase tracking-tight">Erreur de connexion</h4>
        <p class="font-medium opacity-80">{{ errorMessage }}</p>
      </div>
    </div>

    <!-- Table -->
    <div v-else class="bg-white rounded-[2.5rem] border border-slate-200 overflow-hidden shadow-xl shadow-slate-200/50">
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-slate-50/50 border-b border-slate-100">
              <th class="px-8 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Patient</th>
              <th class="px-8 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Coordonnées</th>
              <th class="px-8 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Profil</th>
              <th class="px-8 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Date Naissance</th>
              <th class="px-8 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50">
            <tr v-for="patient in patients" :key="patient.id" class="group hover:bg-slate-50/50 transition-all duration-300">
              <td class="px-8 py-6">
                <div class="flex items-center gap-5">
                  <div class="w-14 h-14 bg-slate-100 text-slate-500 flex items-center justify-center rounded-2xl font-black text-lg border border-slate-200 group-hover:bg-primary group-hover:text-white group-hover:border-primary group-hover:scale-110 transition-all duration-500 shadow-sm">
                    {{ patient.user.nom.charAt(0) }}{{ patient.user.prenom.charAt(0) }}
                  </div>
                  <div>
                    <p class="font-black text-slate-900 group-hover:text-primary transition-colors text-lg tracking-tight">{{ patient.user.nom }} {{ patient.user.prenom }}</p>
                    <p class="text-[10px] text-slate-400 font-black uppercase tracking-widest mt-0.5">Dossier #{{ patient.id }}</p>
                  </div>
                </div>
              </td>
              <td class="px-8 py-6">
                <div class="space-y-1.5">
                  <div class="flex items-center gap-3 text-sm text-slate-600 font-bold">
                    <span class="text-slate-300 group-hover:text-primary transition-colors text-lg">📧</span> {{ patient.user.email }}
                  </div>
                  <div class="flex items-center gap-3 text-sm text-slate-600 font-bold">
                    <span class="text-slate-300 group-hover:text-primary transition-colors text-lg">📞</span> {{ patient.telephone }}
                  </div>
                </div>
              </td>
              <td class="px-8 py-6">
                <span 
                  class="inline-flex items-center px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest border transition-all"
                  :class="patient.sexe.toLowerCase() === 'homme' ? 'bg-blue-50 text-blue-600 border-blue-100 group-hover:bg-blue-600 group-hover:text-white' : 'bg-pink-50 text-pink-600 border-pink-100 group-hover:bg-pink-600 group-hover:text-white'"
                >
                  {{ patient.sexe }}
                </span>
              </td>
              <td class="px-8 py-6">
                <div class="flex items-center gap-3 text-sm text-slate-600 font-black">
                  <span class="text-slate-300 text-xl group-hover:rotate-12 transition-transform">📅</span> {{ new Date(patient.date_naissance).toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' }) }}
                </div>
              </td>
              <td class="px-8 py-6 text-right">
                <button class="p-4 bg-slate-50 hover:bg-primary text-slate-400 hover:text-white rounded-2xl shadow-sm transition-all group-hover:shadow-lg group-hover:shadow-primary/20" title="Voir dossier complet">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                </button>
              </td>
            </tr>

            <tr v-if="patients.length === 0">
              <td colspan="5" class="px-8 py-32 text-center">
                <div class="text-8xl mb-8">📭</div>
                <h3 class="text-2xl font-black text-slate-900 tracking-tight">Aucun patient trouvé</h3>
                <p class="text-slate-500 font-medium max-w-sm mx-auto mt-2">Votre file active est actuellement vide. Les patients apparaîtront ici dès leur admission.</p>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
