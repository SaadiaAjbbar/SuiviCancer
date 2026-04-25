<script setup>
import { ref, onMounted } from 'vue';
import AppCard from '@/components/ui/AppCard.vue';

const conseils = ref([]);
const isLoading = ref(true);
const token = localStorage.getItem('user_token');

const fetchConseils = async () => {
  try {
    const res = await fetch('http://localhost:8080/api/patient/my-conseils', {
      method: 'GET',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      }
    });

    if (res.ok) {
      conseils.value = await res.json();
    }
  } catch (e) {
    console.error("Erreur Connexion API:", e);
  } finally {
    isLoading.value = false;
  }
};

const formatDate = (dateStr) => {
  if (!dateStr) return "";
  return new Date(dateStr).toLocaleDateString('fr-FR', {
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  });
};

onMounted(fetchConseils);
</script>

<template>
  <div class="space-y-10 animate-in fade-in duration-500">
    <div class="flex flex-col gap-2">
      <h1 class="text-3xl font-black text-slate-900 tracking-tight">Conseils & Suivi</h1>
      <p class="text-slate-500 font-medium">Retrouvez ici les recommandations personnalisées de votre équipe médicale.</p>
    </div>

    <div v-if="isLoading" class="py-20 flex flex-col items-center justify-center">
      <div class="w-12 h-12 border-4 border-amber-200 border-t-amber-500 rounded-full animate-spin"></div>
      <p class="mt-4 text-slate-500 font-bold">Chargement de vos recommandations...</p>
    </div>

    <div v-else-if="conseils.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-8">
      <AppCard 
        v-for="c in conseils" 
        :key="c.id" 
        padding="false"
        class="group flex flex-col border-amber-100/50 hover:shadow-xl hover:shadow-amber-500/5 transition-all duration-300"
      >
        <div class="p-6 border-b border-amber-50 bg-amber-50/20">
          <div class="flex items-center justify-between">
            <span class="inline-flex items-center px-3 py-1 bg-amber-100 text-amber-700 text-[10px] font-black uppercase tracking-widest rounded-full">
              Conseil Médical
            </span>
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
              {{ formatDate(c.created_at) }}
            </span>
          </div>
        </div>

        <div class="p-8 flex-1 space-y-8">
          <div class="relative">
            <span class="absolute -top-4 -left-4 text-4xl text-amber-100 font-serif opacity-50">"</span>
            <p class="text-xl font-bold text-slate-800 leading-relaxed pl-2 relative z-10">
              {{ c.description }}
            </p>
            <span class="absolute -bottom-4 right-0 text-4xl text-amber-100 font-serif opacity-50">"</span>
          </div>

          <!-- Doctor Responses Section -->
          <div v-if="c.etatGeneral?.reponses?.length > 0" class="space-y-4">
            <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
               <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></span> Réponses du médecin
            </h4>
            <div class="space-y-3">
              <div v-for="rep in c.etatGeneral.reponses" :key="rep.id" class="p-4 bg-emerald-50/50 border border-emerald-100 rounded-2xl">
                <p class="text-sm font-semibold text-emerald-900 leading-relaxed">{{ rep.message }}</p>
                <p class="text-[10px] font-bold text-emerald-600 mt-2 italic" v-if="rep.medecin?.user">
                  — Dr. {{ rep.medecin.user.nom }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <div class="p-6 bg-slate-50 border-t border-slate-100">
          <div v-if="c.etatGeneral?.consultation?.medecin?.user" class="flex items-center gap-3">
            <div class="w-8 h-8 bg-white border border-slate-200 rounded-full flex items-center justify-center text-xs">👨‍⚕️</div>
            <span class="text-xs font-bold text-slate-500">
              Prescrit par <span class="text-slate-900">Dr. {{ c.etatGeneral.consultation.medecin.user.nom }}</span>
            </span>
          </div>
          <div v-else class="text-xs font-bold text-slate-400 italic">
            Recommandation générale
          </div>
        </div>
      </AppCard>
    </div>

    <div v-else class="py-24 text-center bg-white rounded-3xl border border-dashed border-slate-300">
      <div class="text-7xl mb-6">💡</div>
      <h3 class="text-2xl font-black text-slate-900">Aucun conseil</h3>
      <p class="text-slate-500 font-medium max-w-md mx-auto mt-2">Vous n'avez pas encore reçu de recommandations personnalisées.</p>
    </div>
  </div>
</template>
