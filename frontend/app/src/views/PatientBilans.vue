<script setup>
import { ref, onMounted } from 'vue';
import AppCard from '@/components/ui/AppCard.vue';

const bilans = ref([]);
const isLoading = ref(true);
const token = localStorage.getItem('user_token');

const fetchBilans = async () => {
  try {
    const res = await fetch('http://localhost:8080/api/patient/my-bilans', {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    });
    if (res.ok) {
      bilans.value = await res.json();
    }
  } catch (e) {
    console.error("Erreur bilans:", e);
  } finally {
    isLoading.value = false;
  }
};

const getDay = (date) => new Date(date).getDate();
const getMonth = (date) => new Date(date).toLocaleDateString('fr-FR', { month: 'short' });

onMounted(fetchBilans);
</script>

<template>
  <div class="space-y-10 animate-in fade-in duration-500">
    <div class="flex flex-col gap-2">
      <h1 class="text-3xl font-black text-slate-900 tracking-tight">Mes Bilans de Santé</h1>
      <p class="text-slate-500 font-medium max-w-2xl">Consultez l'historique de vos évaluations médicales et les conclusions de votre médecin.</p>
    </div>

    <div v-if="isLoading" class="py-20 flex flex-col items-center justify-center">
      <div class="w-12 h-12 border-4 border-primary/20 border-t-primary rounded-full animate-spin"></div>
      <p class="mt-4 text-slate-500 font-bold">Analyse de vos données en cours...</p>
    </div>

    <div v-else-if="bilans.length > 0" class="relative pl-8 space-y-12 before:absolute before:left-[11px] before:top-2 before:bottom-2 before:w-[2px] before:bg-slate-100">
      <div v-for="bilan in bilans" :key="bilan.id" class="relative flex gap-8 group">
        <!-- Date Marker -->
        <div class="absolute -left-[32px] top-0 z-10 w-6 h-6 bg-white border-4 border-primary rounded-full group-hover:scale-125 transition-transform"></div>
        
        <div class="flex flex-col items-center min-w-[60px] pt-1">
          <div class="bg-white border-2 border-slate-100 rounded-2xl p-2 w-16 h-16 flex flex-col items-center justify-center shadow-sm group-hover:border-primary/20 transition-colors">
            <span class="text-xl font-black text-slate-900 leading-none">{{ getDay(bilan.created_at) }}</span>
            <span class="text-[10px] font-black text-slate-400 uppercase mt-1">{{ getMonth(bilan.created_at) }}</span>
          </div>
        </div>

        <AppCard class="flex-1 hover:border-primary/20 transition-all duration-300">
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
              <h3 class="text-lg font-black text-slate-900">
                {{ bilan.consultation_id ? 'Résumé de consultation' : 'Suivi médical' }}
              </h3>
              <p class="text-xs font-bold text-primary flex items-center gap-2 mt-1">
                <span class="text-base">👨‍⚕️</span>
                <template v-if="bilan.consultation?.medecin">
                  Dr. {{ bilan.consultation.medecin.user.nom }}
                </template>
                <template v-else-if="bilan.reponse?.patient?.medecin">
                  Dr. {{ bilan.reponse.patient.medecin.user.nom }}
                </template>
              </p>
            </div>
          </div>

          <div class="p-6 bg-slate-50 rounded-2xl border border-slate-100 mb-6">
            <p class="text-slate-700 leading-relaxed font-medium">"{{ bilan.description }}"</p>
          </div>

          <div class="flex flex-wrap gap-2">
            <span v-if="bilan.traitement?.length" class="inline-flex items-center px-3 py-1 bg-blue-50 text-blue-600 text-[10px] font-black uppercase tracking-widest rounded-full border border-blue-100">
              💊 Traitement prescrit
            </span>
            <span v-if="bilan.rendez_vous?.length" class="inline-flex items-center px-3 py-1 bg-emerald-50 text-emerald-600 text-[10px] font-black uppercase tracking-widest rounded-full border border-emerald-100">
              📅 RDV fixé
            </span>
            <span v-if="bilan.conseil?.length" class="inline-flex items-center px-3 py-1 bg-amber-50 text-amber-600 text-[10px] font-black uppercase tracking-widest rounded-full border border-amber-100">
              💡 Conseil ajouté
            </span>
          </div>
        </AppCard>
      </div>
    </div>

    <div v-else class="py-24 text-center bg-white rounded-3xl border border-dashed border-slate-300">
      <div class="text-7xl mb-6">🩺</div>
      <h3 class="text-2xl font-black text-slate-900">Aucun bilan disponible</h3>
      <p class="text-slate-500 font-medium max-w-md mx-auto mt-2">Vos bilans apparaîtront ici dès que votre médecin aura validé vos consultations ou vos réponses.</p>
    </div>
  </div>
</template>
