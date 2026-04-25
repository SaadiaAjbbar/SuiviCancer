<script setup>
import { ref, onMounted } from 'vue';
import AppCard from '@/components/ui/AppCard.vue';

const rdvs = ref([]);
const isLoading = ref(true);
const token = localStorage.getItem('user_token');

const fetchRDVs = async () => {
  try {
    const res = await fetch('http://localhost:8080/api/patient/my-rendez-vous', {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    });
    if (res.ok) {
      rdvs.value = await res.json();
    }
  } catch (e) {
    console.error("Erreur RDV:", e);
  } finally {
    isLoading.value = false;
  }
};

const getDay = (d) => new Date(d).getDate();
const getMonth = (d) => new Date(d).toLocaleDateString('fr-FR', { month: 'short' }).toUpperCase();
const getHour = (d) => new Date(d).toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
const isUpcoming = (d) => new Date(d) > new Date();

onMounted(fetchRDVs);
</script>

<template>
  <div class="space-y-10 animate-in fade-in duration-500">
    <div class="flex flex-col gap-2">
      <h1 class="text-3xl font-black text-slate-900 tracking-tight">Mes Rendez-vous</h1>
      <p class="text-slate-500 font-medium">Retrouvez ici vos consultations programmées et vos suivis passés.</p>
    </div>

    <div v-if="isLoading" class="py-20 flex flex-col items-center justify-center">
      <div class="w-12 h-12 border-4 border-primary/20 border-t-primary rounded-full animate-spin"></div>
      <p class="mt-4 text-slate-500 font-bold">Récupération de vos rendez-vous...</p>
    </div>

    <div v-else-if="rdvs.length > 0" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
      <AppCard 
        v-for="rdv in rdvs" 
        :key="rdv.id" 
        padding="false"
        class="group overflow-hidden border-slate-200/60 hover:shadow-xl hover:shadow-primary/5 transition-all duration-300"
      >
        <div class="p-6 flex items-start gap-5">
          <div class="bg-primary/5 text-primary border border-primary/10 rounded-2xl p-3 w-16 h-16 flex flex-col items-center justify-center group-hover:bg-primary group-hover:text-white transition-all duration-300">
            <span class="text-2xl font-black leading-none">{{ getDay(rdv.date) }}</span>
            <span class="text-[10px] font-black uppercase tracking-tighter mt-1">{{ getMonth(rdv.date) }}</span>
          </div>

          <div class="flex-1 min-w-0">
            <div class="flex items-center justify-between mb-2">
              <span 
                class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-widest"
                :class="isUpcoming(rdv.date) ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-500'"
              >
                {{ isUpcoming(rdv.date) ? 'Programmé' : 'Passé' }}
              </span>
              <span v-if="isUpcoming(rdv.date)" class="text-[10px] font-black text-primary animate-pulse">PROCHAIN</span>
            </div>
            
            <h3 class="font-black text-slate-900 text-lg truncate mb-1">Consultation de suivi</h3>
            <div class="space-y-1">
              <p class="text-xs font-bold text-slate-500 flex items-center gap-2">
                <span class="text-base grayscale opacity-50 group-hover:grayscale-0 group-hover:opacity-100 transition-all">🕒</span> {{ getHour(rdv.date) }}
              </p>
              <p class="text-xs font-bold text-slate-500 flex items-center gap-2" v-if="rdv.etat_general?.consultation?.medecin">
                <span class="text-base grayscale opacity-50 group-hover:grayscale-0 group-hover:opacity-100 transition-all">👨‍⚕️</span> Dr. {{ rdv.etat_general.consultation.medecin.user.nom }}
              </p>
            </div>
          </div>
        </div>

        <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
           <button class="text-xs font-black text-primary hover:underline uppercase tracking-widest">Détails</button>
           <div class="flex -space-x-2">
             <div class="w-6 h-6 rounded-full border-2 border-white bg-slate-200"></div>
             <div class="w-6 h-6 rounded-full border-2 border-white bg-slate-300"></div>
           </div>
        </div>
      </AppCard>
    </div>

    <div v-else class="py-24 text-center bg-white rounded-3xl border border-dashed border-slate-300">
      <div class="text-7xl mb-6">📅</div>
      <h3 class="text-2xl font-black text-slate-900">Aucun rendez-vous</h3>
      <p class="text-slate-500 font-medium max-w-md mx-auto mt-2">Vous n'avez pas encore de rendez-vous programmé. Contactez votre médecin si nécessaire.</p>
    </div>
  </div>
</template>
