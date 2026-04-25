<script setup>
import { ref, onMounted } from 'vue';
import AppCard from '@/components/ui/AppCard.vue';
import AppButton from '@/components/ui/AppButton.vue';
import AppInput from '@/components/ui/AppInput.vue';

const token = localStorage.getItem('user_token');
const questions = ref([]);
const message = ref('');
const isLoading = ref(false);

const isEditingQ = ref(false);
const editIdQ = ref(null);
const formQuestion = ref({ titre: '' });

const fetchQuestions = async () => {
  try {
    const res = await fetch('http://localhost:8080/api/medecin/questions', {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    });
    if (res.ok) {
      questions.value = await res.json();
    }
  } catch (e) {
    console.error("Erreur chargement:", e);
  }
};

const saveQuestion = async () => {
  if (!formQuestion.value.titre.trim()) return;

  isLoading.value = true;
  const method = isEditingQ.value ? 'PUT' : 'POST';
  const url = isEditingQ.value
    ? `http://localhost:8080/api/medecin/questions/${editIdQ.value}`
    : 'http://localhost:8080/api/medecin/questions';

  try {
    const res = await fetch(url, {
      method,
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      },
      body: JSON.stringify(formQuestion.value)
    });

    if (res.ok) {
      message.value = isEditingQ.value ? "✅ Question mise à jour !" : "✅ Question ajoutée !";
      resetFormQ();
      await fetchQuestions();
      setTimeout(() => message.value = '', 3000);
    }
  } catch (e) {
    console.error(e);
  } finally {
    isLoading.value = false;
  }
};

const deleteQuestion = async (id) => {
  if (!confirm("Voulez-vous supprimer cette question ?")) return;

  try {
    const res = await fetch(`http://localhost:8080/api/medecin/questions/${id}`, {
      method: 'DELETE',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    });
    if (res.ok) {
      message.value = "🗑️ Question supprimée";
      await fetchQuestions();
      setTimeout(() => message.value = '', 3000);
    }
  } catch (e) {
    console.error(e);
  }
};

const prepareEditQ = (q) => {
  isEditingQ.value = true;
  editIdQ.value = q.id;
  formQuestion.value.titre = q.titre;
  window.scrollTo({ top: 0, behavior: 'smooth' });
};

const resetFormQ = () => {
  isEditingQ.value = false;
  editIdQ.value = null;
  formQuestion.value.titre = '';
};

onMounted(fetchQuestions);
</script>

<template>
  <div class="space-y-10 animate-in fade-in duration-500 max-w-4xl mx-auto">
    <div v-if="message" class="fixed top-24 right-8 z-50 p-4 bg-primary text-white rounded-2xl shadow-xl animate-in slide-in-from-right-10 font-bold">
      {{ message }}
    </div>

    <div class="flex flex-col gap-2">
      <h1 class="text-3xl font-black text-slate-900 tracking-tight">Gestion des Questions</h1>
      <p class="text-slate-500 font-medium">Définissez les questions auxquelles vos patients devront répondre quotidiennement.</p>
    </div>

    <AppCard>
      <template #header>
        <h3 class="text-lg font-bold text-slate-900">{{ isEditingQ ? 'Modifier la question' : 'Ajouter une nouvelle question' }}</h3>
      </template>
      <form @submit.prevent="saveQuestion" class="flex flex-col sm:flex-row gap-4">
        <div class="flex-1">
          <AppInput
            v-model="formQuestion.titre"
            placeholder="Ex: Comment vous sentez-vous ce matin ?"
            required
            :disabled="isLoading"
          />
        </div>
        <div class="flex gap-2">
          <AppButton type="submit" :loading="isLoading" :variant="isEditingQ ? 'primary' : 'primary'">
            {{ isEditingQ ? 'Mettre à jour' : 'Enregistrer' }}
          </AppButton>
          <AppButton v-if="isEditingQ" variant="secondary" @click="resetFormQ">
            Annuler
          </AppButton>
        </div>
      </form>
    </AppCard>

    <div class="space-y-4">
      <div class="flex items-center justify-between px-2">
        <h3 class="text-sm font-black text-slate-400 uppercase tracking-widest">Banque de Questions ({{ questions.length }})</h3>
      </div>

      <div class="grid grid-cols-1 gap-4">
        <div 
          v-for="(q, index) in questions" 
          :key="q.id" 
          class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between group hover:border-primary/30 transition-all duration-300"
        >
          <div class="flex items-center gap-6">
            <span class="text-2xl font-black text-slate-100 group-hover:text-primary/10 transition-colors">#{{ index + 1 }}</span>
            <p class="text-slate-700 font-bold text-lg">{{ q.titre }}</p>
          </div>
          <div class="flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
            <button @click="prepareEditQ(q)" class="p-3 bg-slate-50 hover:bg-primary/10 text-slate-400 hover:text-primary rounded-xl transition-all" title="Modifier">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
              </svg>
            </button>
            <button @click="deleteQuestion(q.id)" class="p-3 bg-slate-50 hover:bg-red-50 text-slate-400 hover:text-red-500 rounded-xl transition-all" title="Supprimer">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
              </svg>
            </button>
          </div>
        </div>
      </div>

      <div v-if="questions.length === 0" class="py-20 text-center bg-slate-50 rounded-3xl border border-dashed border-slate-300">
        <div class="text-6xl mb-4">💡</div>
        <h3 class="text-lg font-bold text-slate-900">Aucune question définie</h3>
        <p class="text-slate-500">Commencez par ajouter une question pour vos patients.</p>
      </div>
    </div>
  </div>
</template>
