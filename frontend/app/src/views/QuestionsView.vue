<script setup>
import { ref, onMounted } from 'vue';

const token = localStorage.getItem('user_token');
const questions = ref([]);
const message = ref('');
const isLoading = ref(false);

// --- État du Formulaire ---
const isEditingQ = ref(false);
const editIdQ = ref(null);
const formQuestion = ref({ titre: '' });

// 1. READ: Bach n-jibu l-as'ila
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
    } else if (res.status === 403) {
      console.error("403: Vérifie le rôle de l'utilisateur dans la DB.");
    }
  } catch (e) {
    console.error("Erreur chargement:", e);
  }
};

// 2. CREATE & UPDATE: Bach n-diro l-Ajout u l-Modification
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
      await fetchQuestions(); // Refresh list
      setTimeout(() => message.value = '', 3000);
    } else {
      const err = await res.json();
      alert(err.message || "Erreur lors de l'opération");
    }
  } catch (e) {
    console.error(e);
  } finally {
    isLoading.value = false;
  }
};

// 3. DELETE: Bach n-mshou
const deleteQuestion = async (id) => {
  if (!confirm("Bghiti t-mshi had l-question ?")) return;

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

// --- Utils ---
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
  <div class="questions-page">
    <Transition name="fade">
      <div v-if="message" class="alert-toast">{{ message }}</div>
    </Transition>

    <h2 class="main-title">Espace Médecin : Gestion des Questions</h2>

    <section class="card question-form-card">
      <h3 class="title-border">{{ isEditingQ ? 'Modifier la question' : 'Ajouter une nouvelle question' }}</h3>
      <form @submit.prevent="saveQuestion" class="inline-form">
        <div class="input-group">
          <input v-model="formQuestion.titre" placeholder="Kteb l-question dyalk hna..." required :disabled="isLoading">
          <button type="submit" :disabled="isLoading" :class="['btn-save', { 'btn-update': isEditingQ }]">
            {{ isLoading ? '...' : (isEditingQ ? 'Mettre à jour' : 'Enregistrer') }}
          </button>
          <button v-if="isEditingQ" @click="resetFormQ" type="button" class="btn-cancel">
            Annuler
          </button>
        </div>
      </form>
    </section>

    <section class="questions-list-container">
      <div class="list-header">
        <h3>Banque de Questions ({{ questions.length }})</h3>
        <p>Hado homa les questions li ghadi y-jawbo 3lihom l-patients dyalk.</p>
      </div>

      <div class="questions-grid">
        <div v-for="(q, index) in questions" :key="q.id" class="q-item-card">
          <div class="q-content">
            <span class="q-number">#{{ index + 1 }}</span>
            <p>{{ q.titre }}</p>
          </div>
          <div class="q-actions">
            <button @click="prepareEditQ(q)" class="action-btn edit" title="Modifier">✏️</button>
            <button @click="deleteQuestion(q.id)" class="action-btn delete" title="Supprimer">🗑️</button>
          </div>
        </div>
      </div>

      <div v-if="questions.length === 0" class="empty-state">
        <p>Ma kayna hta question. Zid chi wehda l-foug!</p>
      </div>
    </section>
  </div>
</template>

<style scoped>
/* Had l-CSS dyalk khdam mzyan, khalih kima houwa hit m-organisé */
.questions-page {
  max-width: 1000px;
  margin: 0 auto;
  padding: 20px;
}

.main-title {
  color: #1e293b;
  margin-bottom: 30px;
  text-align: center;
}

.alert-toast {
  position: fixed;
  top: 20px;
  right: 20px;
  background: #10b981;
  color: white;
  padding: 12px 24px;
  border-radius: 8px;
  z-index: 1000;
}

.card {
  background: white;
  padding: 1.5rem;
  border-radius: 12px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
  margin-bottom: 2rem;
}

.input-group {
  display: flex;
  gap: 10px;
}

.input-group input {
  flex-grow: 1;
  padding: 12px;
  border: 2px solid #e2e8f0;
  border-radius: 10px;
}

.btn-save {
  background: #3b82f6;
  color: white;
  border: none;
  padding: 0 25px;
  border-radius: 10px;
  cursor: pointer;
}

.btn-update {
  background: #f59e0b;
}

.q-item-card {
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 1rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
}

.action-btn {
  border: none;
  padding: 8px;
  border-radius: 8px;
  cursor: pointer;
  margin-left: 5px;
}

.edit {
  background: #fef3c7;
}

.delete {
  background: #fee2e2;
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.5s;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
