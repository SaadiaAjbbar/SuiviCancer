<script setup>
import { ref, onMounted, computed } from 'vue';
import AppCard from '@/components/ui/AppCard.vue';
import AppButton from '@/components/ui/AppButton.vue';

const userData = JSON.parse(localStorage.getItem('user_data') || '{}');
const token = localStorage.getItem('user_token');
const API_BASE = 'http://localhost:8080/api';

const questions = ref([]);
const history = ref([]);
const responses = ref({});
const isLoading = ref(false);
const isEditing = ref(false);
const editingId = ref(null);

const isAllAnswered = computed(() => {
  return questions.value.length > 0 &&
    Object.values(responses.value).some(val => val && val.trim() !== '');
});

const fetchData = async () => {
  try {
    const headers = { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' };
    const resQ = await fetch(`${API_BASE}/patient/my-questions`, { headers });
    if (resQ.ok) questions.value = await resQ.json();
    await fetchHistory();
  } catch (e) {
    console.error("Erreur chargement données:", e);
  }
};

const fetchHistory = async () => {
  const resH = await fetch(`${API_BASE}/patient/my-history`, {
    headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
  });
  if (resH.ok) history.value = await resH.json();
};

const submitAnswers = async () => {
  if (!isAllAnswered.value) {
    alert("Veuillez remplir au moins une réponse.");
    return;
  }

  isLoading.value = true;
  const formatted = Object.keys(responses.value).map(id => ({
    question_id: parseInt(id),
    reponse: responses.value[id]
  }));

  const url = isEditing.value
    ? `${API_BASE}/patient/responses/${editingId.value}`
    : `${API_BASE}/patient/submit-responses`;

  const method = isEditing.value ? 'PUT' : 'POST';

  try {
    const res = await fetch(url, {
      method,
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      },
      body: JSON.stringify({ responses: formatted })
    });

    if (res.ok) {
      alert(isEditing.value ? "Réponse mise à jour !" : "Réponses envoyées !");
      cancelEdit();
      await fetchHistory();
    }
  } catch (e) {
    console.error(e);
  } finally {
    isLoading.value = false;
  }
};

const prepareEdit = (entry) => {
  isEditing.value = true;
  editingId.value = entry.id;
  responses.value = {};
  entry.reponse_questionnaires.forEach(item => {
    responses.value[item.questions_id] = item.reponse;
  });
  window.scrollTo({ top: 0, behavior: 'smooth' });
};

const deleteResponse = async (id) => {
  if (!confirm("Voulez-vous vraiment supprimer cette réponse ?")) return;
  try {
    const res = await fetch(`${API_BASE}/patient/responses/${id}`, {
      method: 'DELETE',
      headers: { 'Authorization': `Bearer ${token}` }
    });
    if (res.ok) await fetchHistory();
  } catch (e) {
    console.error(e);
  }
};

const cancelEdit = () => {
  isEditing.value = false;
  editingId.value = null;
  responses.value = {};
};

const formatDate = (dateStr) => {
  if (!dateStr) return "";
  return new Date(dateStr).toLocaleDateString('fr-FR', {
    day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit'
  });
};

onMounted(fetchData);
</script>

<template>
  <div class="space-y-10 animate-in fade-in duration-500">
    <!-- Welcome -->
    <div class="bg-primary rounded-3xl p-8 sm:p-10 text-white shadow-xl shadow-primary/20 relative overflow-hidden">
      <div class="relative z-10">
        <h1 class="text-3xl sm:text-4xl font-black tracking-tight">Bonjour, {{ userData.prenom }} 👋</h1>
        <p class="mt-3 text-primary-50/80 text-lg font-medium max-w-xl">
          Suivez votre état de santé en temps réel et restez connecté avec votre équipe médicale.
        </p>
      </div>
      <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full -mr-20 -mt-20 blur-3xl"></div>
      <div class="absolute bottom-0 left-0 w-48 h-48 bg-black/5 rounded-full -ml-10 -mb-10 blur-2xl"></div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
      <!-- Questionnaire Section -->
      <div class="xl:col-span-2 space-y-6">
        <AppCard v-if="questions.length > 0">
          <template #header>
            <div class="flex items-center gap-3">
              <span class="text-2xl">📝</span>
              <h2 class="text-xl font-bold text-slate-900">
                {{ isEditing ? 'Modifier ma réponse' : 'Questionnaire Médical' }}
              </h2>
            </div>
          </template>

          <div class="space-y-8 py-4">
            <div v-for="q in questions" :key="q.id" class="group">
              <label class="block text-sm font-bold text-slate-700 mb-3 group-hover:text-primary transition-colors">
                {{ q.titre }}
              </label>
              <textarea
                v-model="responses[q.id]"
                placeholder="Écrivez votre réponse ici..."
                rows="3"
                class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:bg-white focus:ring-4 focus:ring-primary/10 focus:border-primary outline-none transition-all duration-300 resize-none"
              ></textarea>
            </div>
          </div>

          <template #footer>
            <div class="flex flex-wrap gap-4">
              <AppButton
                @click="submitAnswers"
                :loading="isLoading"
                :disabled="!isAllAnswered"
                custom-class="min-w-[200px]"
              >
                {{ isEditing ? 'Mettre à jour' : 'Envoyer au médecin' }}
              </AppButton>
              <AppButton
                v-if="isEditing"
                variant="secondary"
                @click="cancelEdit"
              >
                Annuler
              </AppButton>
            </div>
          </template>
        </AppCard>

        <div v-else class="p-12 text-center bg-white rounded-3xl border border-dashed border-slate-300">
          <div class="text-5xl mb-4">✨</div>
          <h3 class="text-lg font-bold text-slate-900">Tout est à jour !</h3>
          <p class="text-slate-500">Aucun nouveau questionnaire n'est en attente.</p>
        </div>
      </div>

      <!-- History Section -->
      <div class="space-y-6">
        <h3 class="text-xl font-bold text-slate-900 flex items-center gap-2 px-2">
          <span class="text-xl">📜</span> Historique
        </h3>
        
        <div v-if="history.length > 0" class="space-y-4 overflow-y-auto max-h-[700px] pr-2 custom-scrollbar">
          <AppCard
            v-for="entry in history"
            :key="entry.id"
            padding
            class="hover:border-primary/30 transition-colors"
          >
            <div class="flex justify-between items-start mb-4">
              <span class="inline-flex items-center px-3 py-1 bg-slate-100 text-slate-600 text-xs font-bold rounded-full">
                {{ formatDate(entry.created_at) }}
              </span>
              <div class="flex gap-1">
                <button @click="prepareEdit(entry)" class="p-2 hover:bg-slate-100 rounded-lg text-slate-400 hover:text-primary transition-colors">✏️</button>
                <button @click="deleteResponse(entry.id)" class="p-2 hover:bg-slate-100 rounded-lg text-slate-400 hover:text-red-500 transition-colors">🗑️</button>
              </div>
            </div>

            <div class="space-y-4">
              <div v-for="item in entry.reponse_questionnaires" :key="item.id" class="border-l-2 border-slate-100 pl-4">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">{{ item.question?.titre }}</p>
                <p class="text-sm text-slate-700 mt-1 font-medium leading-relaxed">{{ item.reponse || '(Vide)' }}</p>
              </div>
            </div>
          </AppCard>
        </div>
        
        <div v-else class="p-8 text-center bg-slate-50 rounded-2xl border border-slate-200 border-dashed">
          <p class="text-slate-400 font-medium">Aucun historique disponible.</p>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #e2e8f0;
  border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #cbd5e1;
}
</style>
