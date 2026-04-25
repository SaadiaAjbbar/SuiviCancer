<template>
  <div class="dashboard-container">
    <header class="welcome-section">
      <h1>Bonjour, {{ userData.prenom }} 👋</h1>
      <p>Suivez votre état de santé et répondez aux questions de votre médecin.</p>
    </header>

    <div class="dashboard-grid">

      <section class="card questionnaire-card" v-if="questions.length > 0">
        <div class="card-header">
          <span class="icon">📝</span>
          <h3>{{ isEditing ? 'Modifier ma réponse' : 'Questionnaire Médical' }}</h3>
        </div>

        <div class="questions-list">
          <div v-for="q in questions" :key="q.id" class="q-row-vertical">
            <label class="q-title">{{ q.titre }}</label>
            <textarea v-model="responses[q.id]" placeholder="Écrivez votre réponse ici..." rows="3"></textarea>
          </div>
        </div>

        <div class="form-actions">
          <button @click="submitAnswers" class="btn-submit" :disabled="isLoading">
            {{ isLoading ? 'Opération en cours...' : (isEditing ? 'Mettre à jour' : 'Envoyer au médecin') }}
          </button>
          <button v-if="isEditing" @click="cancelEdit" class="btn-cancel">
            Annuler la modification
          </button>
        </div>
      </section>



      <section class="card history-card">
        <div class="card-header">
          <span class="icon">📜</span>
          <h3>Mon Historique de Suivi</h3>
        </div>

        <div v-if="history.length > 0" class="history-container">
          <div v-for="entry in history" :key="entry.id" class="history-item">
            <div class="item-header">
              <span class="date-tag">Le {{ formatDate(entry.created_at) }}</span>
              <div class="actions">
                <button @click="prepareEdit(entry)" class="btn-action edit" title="Modifier">✏️</button>
                <button @click="deleteResponse(entry.id)" class="btn-action delete" title="Supprimer">🗑️</button>
              </div>
            </div>

            <div class="item-details">
              <div v-for="item in entry.reponse_questionnaires" :key="item.id" class="detail-row">
                <small class="detail-q">{{ item.question?.titre }} :</small>
                <p class="detail-a">{{ item.reponse || '(Vide)' }}</p>
              </div>
            </div>
          </div>
        </div>
        <div v-else class="no-data">Aucun historique disponible.</div>
      </section>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';

// --- Configuration & State ---
const userData = JSON.parse(localStorage.getItem('user_data') || '{}');
const token = localStorage.getItem('user_token');
const API_BASE = 'http://localhost:8080/api';

const questions = ref([]);
const history = ref([]);
const latestConsultation = ref(null);
const responses = ref({});
const isLoading = ref(false);
const isEditing = ref(false);
const editingId = ref(null);

// --- Computed ---
const isAllAnswered = computed(() => {
  return questions.value.length > 0 &&
    Object.values(responses.value).some(val => val && val.trim() !== '');
});

// --- Logic ---

const fetchData = async () => {
  try {
    const headers = { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' };

    // 1. Questions du médecin
    const resQ = await fetch(`${API_BASE}/patient/my-questions`, { headers });
    if (resQ.ok) questions.value = await resQ.json();

    // 2. Historique des réponses
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

<style scoped>
.dashboard-container {
  max-width: 1100px;
  margin: 0 auto;
  padding: 20px;
}

.welcome-section {
  margin-bottom: 30px;
}

.dashboard-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
  gap: 25px;
}

.card {
  background: white;
  padding: 20px;
  border-radius: 15px;
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
  border-top: 5px solid #3498db;
}

.card-header {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 20px;
}

.card-header h3 {
  margin: 0;
  color: #2c3e50;
}

.questionnaire-card {
  grid-column: span 2;
  border-top-color: #e67e22;
}

.q-row-vertical {
  display: flex;
  flex-direction: column;
  gap: 8px;
  margin-bottom: 20px;
}

.q-title {
  font-weight: 600;
  color: #34495e;
}

textarea {
  width: 100%;
  border: 2px solid #edeff2;
  border-radius: 10px;
  padding: 12px;
  font-family: inherit;
  transition: border-color 0.3s;
  resize: vertical;
}

textarea:focus {
  border-color: #e67e22;
  outline: none;
}

.form-actions {
  display: flex;
  gap: 10px;
  margin-top: 10px;
}

.btn-submit {
  flex: 2;
  background: #e67e22;
  color: white;
  border: none;
  padding: 12px;
  border-radius: 8px;
  cursor: pointer;
  font-weight: bold;
}

.btn-cancel {
  flex: 1;
  background: #f1f2f6;
  color: #57606f;
  border: none;
  padding: 12px;
  border-radius: 8px;
  cursor: pointer;
}

.history-card {
  grid-column: span 2;
  border-top-color: #9b59b6;
}

.history-item {
  background: #f9fbfc;
  border-radius: 12px;
  padding: 15px;
  margin-bottom: 15px;
  border-left: 5px solid #9b59b6;
}

.item-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 15px;
}

.date-tag {
  font-weight: bold;
  color: #2c3e50;
  font-size: 0.9rem;
}

.btn-action {
  background: white;
  border: 1px solid #ddd;
  padding: 5px 8px;
  border-radius: 5px;
  cursor: pointer;
  margin-left: 5px;
}

.btn-action.delete:hover {
  background: #fee2e2;
  color: #ef4444;
}

.detail-row {
  margin-bottom: 10px;
}

.detail-q {
  color: #7f8c8d;
  display: block;
  margin-bottom: 2px;
}

.detail-a {
  margin: 0;
  color: #2c3e50;
  font-weight: 500;
}

.badge {
  padding: 4px 8px;
  border-radius: 5px;
  font-size: 0.75rem;
  font-weight: bold;
}

.badge.grave {
  background: #fee2e2;
  color: #ef4444;
}

.no-data {
  text-align: center;
  color: #95a5a6;
  padding: 20px;
  font-style: italic;
}

@media (max-width: 800px) {

  .questionnaire-card,
  .history-card {
    grid-column: span 1;
  }
}
</style>
