<script setup>
import { ref, onMounted } from 'vue';
import AppCard from '@/components/ui/AppCard.vue';

const token = localStorage.getItem('user_token');
const medecinData = JSON.parse(localStorage.getItem('user_info') || '{}');

const stats = ref({
  consultations: 0,
  patients: 0,
  questions: 0
});

const fetchData = async () => {
  try {
    const headers = { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' };
    const [resC, resP, resQ] = await Promise.all([
      fetch('http://localhost:8080/api/medecin/consultations', { headers }),
      fetch('http://localhost:8080/api/medecin/my-patients', { headers }),
      fetch('http://localhost:8080/api/medecin/questions', { headers })
    ]);

    const consultations = await resC.json();
    const patients = await resP.json();
    const questions = await resQ.json();

    stats.value = {
      consultations: consultations.length || 0,
      patients: patients.length || 0,
      questions: questions.length || 0
    };
  } catch (e) {
    console.error("Erreur stats:", e);
  }
};

onMounted(() => fetchData());

const statCards = [
  { label: 'Consultations', key: 'consultations', icon: '📅', color: 'text-blue-600', bg: 'bg-blue-50', border: 'border-blue-100' },
  { label: 'Patients Suivis', key: 'patients', icon: '👥', color: 'text-emerald-600', bg: 'bg-emerald-50', border: 'border-emerald-100' },
  { label: 'Questions FAQ', key: 'questions', icon: '❓', color: 'text-amber-600', bg: 'bg-amber-50', border: 'border-amber-100' },
];
</script>

<template>
  <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500">
    <div class="flex flex-col gap-1">
      <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Bonjour, Dr. {{ medecinData.nom }} 👋</h1>
      <p class="text-slate-500 font-medium">Voici un aperçu de votre activité aujourd'hui.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div 
        v-for="stat in statCards" 
        :key="stat.key"
        class="bg-white p-6 rounded-2xl border shadow-sm transition-all duration-300 hover:shadow-md hover:-translate-y-1"
        :class="stat.border"
      >
        <div class="flex items-center gap-4">
          <div class="w-14 h-14 flex items-center justify-center rounded-2xl text-2xl" :class="stat.bg">
            {{ stat.icon }}
          </div>
          <div>
            <p class="text-sm font-semibold text-slate-500 uppercase tracking-wider">{{ stat.label }}</p>
            <h3 class="text-3xl font-black text-slate-900">{{ stats[stat.key] }}</h3>
          </div>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mt-12">
      <AppCard>
        <template #header>
          <div class="flex items-center justify-between">
            <h2 class="text-lg font-bold text-slate-900">Activité Récente</h2>
            <button class="text-primary text-sm font-bold hover:underline">Voir tout</button>
          </div>
        </template>
        <div class="space-y-4">
          <div v-if="stats.consultations === 0" class="py-12 text-center">
            <div class="text-4xl mb-2">🍃</div>
            <p class="text-slate-400 font-medium">Aucune activité récente à afficher.</p>
          </div>
          <div v-else class="space-y-4">
             <!-- Placeholder for recent activity items -->
             <div v-for="i in 3" :key="i" class="flex items-center gap-4 p-3 rounded-xl hover:bg-slate-50 transition-colors">
               <div class="w-10 h-10 bg-slate-100 rounded-full flex items-center justify-center text-lg">👤</div>
               <div class="flex-1">
                 <p class="text-sm font-bold text-slate-900">Consultation avec Patient #00{{i}}</p>
                 <p class="text-xs text-slate-500 font-medium">Il y a {{i}} heure(s)</p>
               </div>
               <span class="px-2 py-1 bg-blue-50 text-blue-600 text-[10px] font-bold rounded uppercase">Terminé</span>
             </div>
          </div>
        </div>
      </AppCard>

      <AppCard>
        <template #header>
          <h2 class="text-lg font-bold text-slate-900">Conseils du Jour</h2>
        </template>
        <div class="space-y-4">
          <div class="p-4 bg-primary/5 border border-primary/10 rounded-2xl flex gap-4">
            <div class="text-2xl">💡</div>
            <div>
              <p class="font-bold text-primary">Optimisez votre suivi</p>
              <p class="text-sm text-slate-600 leading-relaxed mt-1">Pensez à répondre aux questions FAQ pour réduire les appels téléphoniques directs des patients.</p>
            </div>
          </div>
          <div class="p-4 bg-amber-50 border border-amber-100 rounded-2xl flex gap-4">
            <div class="text-2xl">⚡</div>
            <div>
              <p class="font-bold text-amber-700">Rappel Administratif</p>
              <p class="text-sm text-slate-600 leading-relaxed mt-1">Vous avez des rapports en attente de validation pour la semaine dernière.</p>
            </div>
          </div>
        </div>
      </AppCard>
    </div>
  </div>
</template>
