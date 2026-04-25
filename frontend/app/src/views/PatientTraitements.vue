<script setup>
import { ref, onMounted } from 'vue';
import AppCard from '@/components/ui/AppCard.vue';

const traitements = ref([]);
const isLoading = ref(true);
const token = localStorage.getItem('user_token');

const fetchTraitements = async () => {
  try {
    const res = await fetch('http://localhost:8080/api/patient/my-traitements', {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    });
    if (res.ok) {
      traitements.value = await res.json();
    }
  } catch (e) {
    console.error("Erreur traitements:", e);
  } finally {
    isLoading.value = false;
  }
};

const formatDate = (dateStr) => {
  return new Date(dateStr).toLocaleDateString('fr-FR', {
    day: 'numeric', month: 'long', year: 'numeric'
  });
};

onMounted(fetchTraitements);
</script>

<template>
  <div class="space-y-10 animate-in fade-in duration-500">
    <div class="flex flex-col gap-2">
      <h1 class="text-3xl font-black text-slate-900 tracking-tight">Mes Traitements</h1>
      <p class="text-slate-500 font-medium">Retrouvez ici les médicaments et soins prescrits par votre médecin.</p>
    </div>

    <div v-if="isLoading" class="py-20 flex flex-col items-center justify-center">
      <div class="w-12 h-12 border-4 border-primary/20 border-t-primary rounded-full animate-spin"></div>
      <p class="mt-4 text-slate-500 font-bold">Chargement de vos traitements...</p>
    </div>

    <div v-else-if="traitements.length > 0" class="grid grid-cols-1 lg:grid-cols-2 gap-8">
      <AppCard 
        v-for="t in traitements" 
        :key="t.id" 
        padding="false"
        class="group flex flex-col hover:border-primary/20 transition-all duration-300"
      >
        <div class="p-6 border-b border-slate-50 bg-slate-50/30">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
              <div class="w-12 h-12 bg-blue-50 text-blue-600 flex items-center justify-center rounded-2xl text-2xl group-hover:bg-blue-600 group-hover:text-white transition-all duration-300">
                💊
              </div>
              <div>
                <h3 class="font-black text-slate-900 text-lg uppercase tracking-tight">{{ t.nom }}</h3>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1">Prescrit le : {{ formatDate(t.created_at) }}</p>
              </div>
            </div>
            <div class="px-3 py-1 bg-blue-50 text-blue-600 text-[10px] font-black uppercase tracking-widest rounded-full border border-blue-100">
              Actif
            </div>
          </div>
        </div>

        <div class="p-6 flex-1">
          <div class="space-y-3">
            <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Instructions Médicales</h4>
            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
              <p class="text-slate-700 leading-relaxed font-medium italic">"{{ t.description }}"</p>
            </div>
          </div>
        </div>

        <div class="p-6 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
           <div class="flex items-center gap-2">
             <span class="text-xs font-black text-primary bg-primary/5 px-3 py-1 rounded-full border border-primary/10">
               <template v-if="t.etat_general?.consultation?.medecin">
                 Dr. {{ t.etat_general.consultation.medecin.user.nom }}
               </template>
               <template v-else-if="t.etat_general?.reponse?.patient?.medecin">
                 Dr. {{ t.etat_general.reponse.patient.medecin.user.nom }}
               </template>
               <template v-else>
                 Médecin partenaire
               </template>
             </span>
           </div>
           <button class="text-[10px] font-black text-slate-400 hover:text-primary transition-colors uppercase tracking-widest">Voir ordonnance</button>
        </div>
      </AppCard>
    </div>

    <div v-else class="py-24 text-center bg-white rounded-3xl border border-dashed border-slate-300">
      <div class="text-7xl mb-6">💊</div>
      <h3 class="text-2xl font-black text-slate-900">Aucun traitement</h3>
      <p class="text-slate-500 font-medium max-w-md mx-auto mt-2">Vous n'avez pas de traitement actif pour le moment. Suivez bien les conseils de votre médecin.</p>
    </div>
  </div>
</template>
