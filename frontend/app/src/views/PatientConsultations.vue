<script setup>
import { ref, onMounted } from 'vue';
import AppCard from '@/components/ui/AppCard.vue';

const consultations = ref([]);
const isLoading = ref(true);
const token = localStorage.getItem('user_token');

const fetchConsultations = async () => {
  try {
    const res = await fetch('http://localhost:8080/api/patient/my-consultations', {
      headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
    });
    if (res.ok) consultations.value = await res.json();
  } catch (e) {
    console.error(e);
  } finally {
    isLoading.value = false;
  }
};

const getDay = (d) => new Date(d).getDate();
const getMonth = (d) => new Date(d).toLocaleDateString('fr-FR', { month: 'short' }).toUpperCase();

onMounted(fetchConsultations);
</script>

<template>
  <div class="space-y-10 animate-in fade-in duration-500">
    <div class="flex flex-col gap-2">
      <h1 class="text-3xl font-black text-slate-900 tracking-tight">Mon Historique Médical</h1>
      <p class="text-slate-500 font-medium">Retrouvez le détail de vos visites et les décisions prises par votre équipe médicale.</p>
    </div>

    <div v-if="isLoading" class="py-20 flex flex-col items-center justify-center">
      <div class="w-12 h-12 border-4 border-primary/20 border-t-primary rounded-full animate-spin"></div>
      <p class="mt-4 text-slate-500 font-bold">Chargement de votre dossier...</p>
    </div>

    <div v-else-if="consultations.length > 0" class="space-y-8">
      <div v-for="c in consultations" :key="c.id" class="flex flex-col lg:flex-row bg-white rounded-3xl overflow-hidden border border-slate-200/60 shadow-sm hover:shadow-xl hover:shadow-primary/5 transition-all duration-500">
        <!-- Sidebar with Date -->
        <div class="lg:w-40 bg-slate-50 p-8 flex flex-col items-center justify-center border-b lg:border-b-0 lg:border-r border-slate-100">
          <div class="text-center mb-4">
            <span class="block text-4xl font-black text-slate-900 leading-none">{{ getDay(c.date) }}</span>
            <span class="block text-xs font-black text-primary uppercase tracking-widest mt-2">{{ getMonth(c.date) }}</span>
          </div>
          <span 
            class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest border"
            :class="c.gravite?.toLowerCase() === 'urgente' || c.gravite?.toLowerCase() === 'sévère' ? 'bg-red-50 text-red-600 border-red-100' : 'bg-emerald-50 text-emerald-600 border-emerald-100'"
          >
            {{ c.gravite || 'Normal' }}
          </span>
        </div>

        <!-- Main Content -->
        <div class="flex-1 p-8 sm:p-10">
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
            <h3 class="text-2xl font-black text-slate-900 tracking-tight">Consultation avec Dr. {{ c.medecin?.user?.nom }}</h3>
            <div class="flex flex-wrap gap-2">
              <span v-for="s in c.symptomes" :key="s.id" class="px-3 py-1 bg-slate-100 text-slate-600 text-[10px] font-black uppercase tracking-widest rounded-full">
                🩹 {{ s.nom }}
              </span>
              <span v-for="t in c.toxicites" :key="t.id" class="px-3 py-1 bg-amber-50 text-amber-600 text-[10px] font-black uppercase tracking-widest rounded-full border border-amber-100">
                ⚠️ {{ t.nom }}
              </span>
            </div>
          </div>

          <div v-if="c.etat_general" class="space-y-6">
            <div class="p-6 bg-slate-50 rounded-3xl border border-slate-100 relative">
              <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 flex items-center gap-2">
                <span class="w-2 h-2 bg-primary rounded-full"></span> Observations du médecin
              </h4>
              <p class="text-slate-700 leading-relaxed font-medium italic">"{{ c.etat_general.description }}"</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
              <div v-if="c.etat_general.traitement?.length" class="flex items-center gap-3 p-4 bg-blue-50/50 border border-blue-100 rounded-2xl">
                <span class="text-2xl">💊</span>
                <div>
                  <p class="text-[10px] font-black text-blue-400 uppercase tracking-widest">Traitement</p>
                  <p class="text-xs font-bold text-blue-700">{{ c.etat_general.traitement.length }} prescrit(s)</p>
                </div>
              </div>
              <div v-if="c.etat_general.rendez_vous?.length" class="flex items-center gap-3 p-4 bg-emerald-50/50 border border-emerald-100 rounded-2xl">
                <span class="text-2xl">📅</span>
                <div>
                  <p class="text-[10px] font-black text-emerald-400 uppercase tracking-widest">Prochain RDV</p>
                  <p class="text-xs font-bold text-emerald-700">Programmé</p>
                </div>
              </div>
              <div v-if="c.etat_general.conseil?.length" class="flex items-center gap-3 p-4 bg-amber-50/50 border border-amber-100 rounded-2xl">
                <span class="text-2xl">💡</span>
                <div>
                  <p class="text-[10px] font-black text-amber-400 uppercase tracking-widest">Conseils</p>
                  <p class="text-xs font-bold text-amber-700">Disponibles</p>
                </div>
              </div>
            </div>
          </div>
          
          <div v-else class="py-10 text-center bg-slate-50/50 rounded-3xl border border-dashed border-slate-200">
            <p class="text-slate-400 font-bold italic">En attente du compte-rendu détaillé du médecin...</p>
          </div>
        </div>
      </div>
    </div>

    <div v-else class="py-24 text-center bg-white rounded-3xl border border-dashed border-slate-300">
      <div class="text-7xl mb-6">🏥</div>
      <h3 class="text-2xl font-black text-slate-900">Aucune consultation</h3>
      <p class="text-slate-500 font-medium max-w-md mx-auto mt-2">Votre historique de consultations apparaîtra ici après votre première visite.</p>
    </div>
  </div>
</template>
