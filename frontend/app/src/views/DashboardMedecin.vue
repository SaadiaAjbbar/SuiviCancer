<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();
const token = localStorage.getItem('user_token');
const medecinData = JSON.parse(localStorage.getItem('user_info') || '{}');

const currentTab = ref('questions');
const message = ref('');

// --- DONNÉES ---
const questions = ref([]);
const consultations = ref([]);
const patients = ref([]);
const toxicitesList = ref([]);
const symptomesList = ref([]);

// --- NOUVEAU FORM: ETAT GENERAL & DECISIONS ---
const showEtatModal = ref(false); // Bach n-7ello/n-seddo l-modal
const selectedConsultation = ref(null); // Bach n-3rfo ina consultation khdamين 3liha

const formEtat = ref({
  description: '',
  consultation_id: '',
  // Les décisions (Checkboxes)
  hasRDV: false, rdv_date: '',
  hasTraitement: false, tr_nom: '', tr_desc: '',
  hasConseil: false, cons_desc: ''
});

// 1. Bach n-7ello l-formulaire dyal l-etat general
const openEtatForm = (consult) => {
  selectedConsultation.value = consult;
  formEtat.value.consultation_id = consult.id;
  showEtatModal.value = true;
};
// 1. Prepare Edit (bach t-3mmer l-modal b dakchi li fat)
const isEditingEtat = ref(false);
const editEtatId = ref(null);

const prepareEditEtat = (consult) => {
  isEditingEtat.value = true;
  editEtatId.value = consult.etat_general.id;
  selectedConsultation.value = consult;

  // N-3mmro l-form b l-data li kayna f DB
  formEtat.value = {
    description: consult.etat_general.description,
    consultation_id: consult.id,
    // Hna khass l-backend ikoun i-rj3 hta l-decisions bach t-modifihom (ila briti)
    hasRDV: false,
    hasTraitement: false,
    hasConseil: false
  };
  showEtatModal.value = true;
};

// 2. Modify saveEtatTotal bach t-supporti l-Update
const saveEtatTotal = async () => {
  const method = isEditingEtat.value ? 'PUT' : 'POST';
  const url = isEditingEtat.value
    ? `http://localhost:8080/api/medecin/etat-general/${editEtatId.value}`
    : 'http://localhost:8080/api/medecin/etat-general';

  try {
    const res = await fetch(url, {
      method,
      headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${token}` },
      body: JSON.stringify({
        description: formEtat.value.description,
        consultation_id: formEtat.value.consultation_id
      })
    });

    if (res.ok) {
      // Ila kan update o kano des decisions jdad, t-9der t-zidhom hna...
      message.value = "✅ État mis à jour !";
      showEtatModal.value = false;
      isEditingEtat.value = false;
      fetchData(); // Refresh la liste
    }
  } catch (e) { console.error(e); }
};

// 3. Supprimer l'Etat General
const deleteEtatGeneral = async (id) => {
  if (!confirm("Supprimer l'état général et ses décisions ?")) return;
  try {
    const res = await fetch(`http://localhost:8080/api/medecin/etat-general/${id}`, {
      method: 'DELETE',
      headers: { 'Authorization': `Bearer ${token}` }
    });
    if (res.ok) {
      message.value = "🗑️ État supprimé !";
      fetchData();
    }
  } catch (e) { console.error(e); }
};
// --- FORMS ---
const formQuestion = ref({ titre: '' });
const formConsultation = ref({
  patient_id: '',
  date: '',
  gravite: 'Normal',
  toxicite_ids: [],
  symptome_ids: []
});

const isEditingQ = ref(false); // Pour les questions
const editIdQ = ref(null);

const isEditingC = ref(false); // Pour les consultations
const editIdC = ref(null);

// --- CHARGEMENT ---
const fetchData = async () => {
  try {
    const headers = { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' };
    const resQ = await fetch('http://localhost:8080/api/medecin/questions', { headers });
    questions.value = await resQ.json();
    const resC = await fetch('http://localhost:8080/api/medecin/consultations', { headers });
    consultations.value = await resC.json();
    const resP = await fetch('http://localhost:8080/api/medecin/my-patients', { headers });
    patients.value = await resP.json();
    const resT = await fetch('http://localhost:8080/api/medecin/toxicites', { headers });
    toxicitesList.value = await resT.json();
    const resS = await fetch('http://localhost:8080/api/medecin/symptomes', { headers });
    if (resS.ok) symptomesList.value = await resS.json();
  } catch (e) { console.error("Erreur chargement", e); }
};

// --- ACTIONS CONSULTATION (CREATE & UPDATE) ---
const saveConsultation = async () => {
  const method = isEditingC.value ? 'PUT' : 'POST';
  const url = isEditingC.value
    ? `http://localhost:8080/api/medecin/consultations/${editIdC.value}`
    : 'http://localhost:8080/api/medecin/consultations';

  try {
    const res = await fetch(url, {
      method,
      headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' },
      body: JSON.stringify(formConsultation.value)
    });

    if (res.ok) {
      message.value = isEditingC.value ? "Consultation modifiée !" : "Consultation enregistrée !";
      resetConsultForm();
      fetchData();
    } else { message.value = "Erreur (422): Vérifiez les données"; }
  } catch (e) { console.error(e); }
};

const deleteConsultation = async (id) => {
  // Check awal: t-akked bli l-ID jayi s7i7
  if (!id) return;

  if (!confirm("Êtes-vous sûr de vouloir supprimer cette consultation ?")) return;

  try {
    const res = await fetch(`http://localhost:8080/api/medecin/consultations/${id}`, {
      method: 'DELETE',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    });

    if (res.ok) {
      message.value = "Consultation supprimée avec succès !";
      fetchData(); // Bach t-rafréchi l-liste
    } else {
      const err = await res.json();
      message.value = "Erreur: " + (err.message || "Suppression impossible");
    }
  } catch (e) {
    console.error("Erreur réseau:", e);
    message.value = "Erreur de connexion au serveur.";
  }
};

const prepareEditConsult = (c) => {
  isEditingC.value = true;
  editIdC.value = c.id;
  formConsultation.value = {
    patient_id: c.patient_id,
    date: c.date.replace(' ', 'T'), // Format input datetime-local
    gravite: c.gravite,
    toxicite_ids: c.toxicites.map(t => t.id),
    symptome_ids: c.symptomes.map(s => s.id)
  };
};

const resetConsultForm = () => {
  isEditingC.value = false;
  editIdC.value = null;
  formConsultation.value = { patient_id: '', date: '', gravite: 'Normal', toxicite_ids: [], symptome_ids: [] };
};

// --- ACTIONS QUESTIONS (Existant) ---
const saveQuestion = async () => {
  const method = isEditingQ.value ? 'PUT' : 'POST';
  const url = isEditingQ.value ? `http://localhost:8080/api/medecin/questions/${editIdQ.value}` : 'http://localhost:8080/api/medecin/questions';
  const res = await fetch(url, {
    method,
    headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' },
    body: JSON.stringify(formQuestion.value)
  });
  if (res.ok) { resetFormQ(); fetchData(); }
};

const deleteQuestion = async (id) => {
  if (!confirm("Supprimer ?")) return;
  await fetch(`http://localhost:8080/api/medecin/questions/${id}`, { method: 'DELETE', headers: { 'Authorization': `Bearer ${token}` } });
  fetchData();
};

const prepareEditQ = (q) => { isEditingQ.value = true; editIdQ.value = q.id; formQuestion.value.titre = q.titre; };
const resetFormQ = () => { isEditingQ.value = false; editIdQ.value = null; formQuestion.value.titre = ''; };
const handleLogout = () => { localStorage.clear(); router.push('/login'); };

onMounted(() => { fetchData(); });
</script>

<template>
  <div class="medecin-container">
    <nav class="navbar">
      <div class="logo">E-Sante | Dr. {{ medecinData.nom }}</div>
      <ul class="nav-links">
        <li @click="currentTab = 'questions'" :class="{ active: currentTab === 'questions' }">Questions</li>
        <li @click="currentTab = 'consultations'" :class="{ active: currentTab === 'consultations' }">Consultations</li>
      </ul>
      <button class="btn-logout" @click="handleLogout">Déconnexion</button>
    </nav>

    <div class="content">
      <div v-if="message" class="alert">{{ message }}</div>

      <div v-if="currentTab === 'questions'">
        <section class="card form-section">
          <h3>{{ isEditingQ ? 'Modifier la question' : 'Ajouter une question' }}</h3>
          <form @submit.prevent="saveQuestion" class="form-inline">
            <input v-model="formQuestion.titre" placeholder="Titre..." required>
            <button type="submit" class="btn-save">{{ isEditingQ ? 'Mettre à jour' : 'Enregistrer' }}</button>
            <button v-if="isEditingQ" @click="resetFormQ" type="button" class="btn-cancel">Annuler</button>
          </form>
        </section>
        <div class="questions-list">
          <div v-for="q in questions" :key="q.id" class="question-item">
            <p>{{ q.titre }}</p>
            <div>
              <button @click="prepareEditQ(q)" class="btn-edit-sm">Modifier</button>
              <button @click="deleteQuestion(q.id)" class="btn-del-sm">Supprimer</button>
            </div>
          </div>
        </div>
      </div>

      <div v-if="currentTab === 'consultations'">
        <div class="consult-layout">
          <section class="card consult-form-card">
            <h3 class="section-title">{{ isEditingC ? 'Modifier Consultation' : 'Nouvelle Consultation' }}</h3>
            <form @submit.prevent="saveConsultation" class="modern-form">
              <div class="form-row">
                <div class="field">
                  <label>Patient</label>
                  <select v-model="formConsultation.patient_id" :disabled="isEditingC" required>
                    <option v-for="p in patients" :key="p.id" :value="p.id">{{ p.user?.nom }} {{ p.user?.prenom }}
                    </option>
                  </select>
                </div>
                <div class="field">
                  <label>Date</label>
                  <input type="datetime-local" v-model="formConsultation.date" required>
                </div>
              </div>
              <div class="field">
                <label>Gravité</label>
                <div class="gravity-selector">
                  <label v-for="g in ['Normal', 'Modéré', 'Sévère']" :key="g"
                    :class="['radio-label', { active: formConsultation.gravite === g }]">
                    <input type="radio" :value="g" v-model="formConsultation.gravite"> {{ g }}
                  </label>
                </div>
              </div>
              <div class="form-row">
                <div class="field">
                  <label>Toxicités</label>
                  <select v-model="formConsultation.toxicite_ids" multiple class="multi-select">
                    <option v-for="t in toxicitesList" :key="t.id" :value="t.id">{{ t.nom }}</option>
                  </select>
                </div>
                <div class="field">
                  <label>Symptômes</label>
                  <select v-model="formConsultation.symptome_ids" multiple class="multi-select">
                    <option v-for="s in symptomesList" :key="s.id" :value="s.id">{{ s.nom }}</option>
                  </select>
                </div>
              </div>
              <button type="submit" class="btn-primary">{{ isEditingC ? 'Enregistrer les modifications' : 'Créer'
                }}</button>
              <button v-if="isEditingC" @click="resetConsultForm" type="button" class="btn-cancel-lg">Annuler</button>
            </form>
          </section>


          <section class="consult-list-container">
            <h3 class="section-title">Historique</h3>
            <div v-for="c in consultations" :key="c.id" class="consult-card">
              <div class="card-header">
                <strong>{{ c.patient?.user?.nom }} {{ c.patient?.user?.prenom }}</strong>
                <div class="card-tags">
                  <span v-for="t in c.toxicites" :key="t.id" class="tag tox">{{ t.nom }}</span>
                  <span v-for="s in c.symptomes" :key="s.id" class="tag sym">{{ s.nom }}</span>
                </div>

                <div v-if="consultations.length === 0" class="empty-msg">Aucune consultation.</div>
                <div class="actions-icons">
                  <button @click="prepareEditConsult(c)" class="btn-update">modifier</button>
                  <button @click="deleteConsultation(c.id)" class="btn-del">supprimer</button>
                  <button v-if="!c.etat_general" @click="openEtatForm(c)" class="btn-action-etat" title="Ajouter État">
                    +etat general
                  </button>
                  <div v-else class="etat-actions">
                    <button @click="prepareEditEtat(c)" class="btn-edit-sm" title="Modifier l'État">📝</button>
                    <button @click="deleteEtatGeneral(c.etat_general.id)" class="btn-del-sm"
                      title="Supprimer l'État">✖</button>
                  </div>


                </div>
              </div>
              <div class="card-body">
                <span :class="'status-badge ' + c.gravite.toLowerCase()">{{ c.gravite }}</span>
                <p class="date"> {{ new Date(c.date).toLocaleString() }}</p>
              </div>
            </div>
          </section>
        </div>
      </div>
    </div>
    <div v-if="showEtatModal" class="modal-overlay">
      <div class="modal-content card shadow-lg">
        <h3>Bilan pour: {{ selectedConsultation?.patient?.user?.nom }}</h3>
        <form @submit.prevent="saveEtatTotal">
          <div class="field">
            <label>Description de l'État :</label>
            <textarea v-model="formEtat.description" required class="form-control"></textarea>
          </div>

          <div class="decisions-grid">
            <div class="decision-item">
              <label><input type="checkbox" v-model="formEtat.hasRDV"> Rendez-vous</label>
              <input v-if="formEtat.hasRDV" type="datetime-local" v-model="formEtat.rdv_date" required>
            </div>
            <div class="decision-item">
              <label><input type="checkbox" v-model="formEtat.hasTraitement"> Traitement</label>
              <div v-if="formEtat.hasTraitement">
                <input v-model="formEtat.tr_nom" placeholder="Médicament" required>
                <textarea v-model="formEtat.tr_desc" placeholder="Détails..."></textarea>
              </div>
            </div>
          </div>
          <div class="decision-item">
            <label>
              <input type="checkbox" v-model="formEtat.hasConseil"> Donner un Conseil
            </label>
            <div v-if="formEtat.hasConseil" class="sub-field">
              <textarea v-model="formEtat.cons_desc" placeholder="Kteb hna l-nasiha (ex: Chrob l-ma bzaf, rta7...)"
                required></textarea>
            </div>
          </div>

          <div class="modal-buttons">
            <button type="submit" class="btn-save">Enregistrer tout</button>
            <button type="button" @click="showEtatModal = false" class="btn-cancel">Annuler</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* On garde tes styles et on rajoute les nouveaux pour les boutons */
.consult-layout {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
}

.actions-icons {
  display: flex;
  gap: 10px;
}

.btn-update {
  background: rgb(205, 140, 50);
  border-radius: 10px;
  width: 100px;
  border: none;
  cursor: pointer;
  font-size: 1.2rem;
  transition: 0.2s;
}

.btn-update:hover {
  transform: scale(1.2);
}

.btn-del {
  filter: grayscale(1);
}

.btn-del:hover {
  filter: grayscale(0);
}

.radio-label {
  flex: 1;
  text-align: center;
  padding: 8px;
  border: 1px solid #ddd;
  border-radius: 6px;
  cursor: pointer;
}

.radio-label.active {
  background: #3498db;
  color: white;
  border-color: #3498db;
}

.radio-label input {
  display: none;
}

.btn-primary {
  width: 100%;
  padding: 10px;
  background: #27ae60;
  color: white;
  border: none;
  border-radius: 6px;
  margin-top: 10px;
  cursor: pointer;
}

.btn-cancel-lg {
  width: 100%;
  padding: 10px;
  background: #95a5a6;
  color: white;
  border: none;
  border-radius: 6px;
  margin-top: 5px;
  cursor: pointer;
}

.multi-select {
  height: 100px;
  width: 100%;
  border-radius: 6px;
  border: 1px solid #ddd;
}

.status-badge {
  font-size: 0.7rem;
  padding: 2px 8px;
  border-radius: 10px;
  text-transform: uppercase;
}

.status-badge.normal {
  background: #d4edda;
  color: #155724;
}

.status-badge.modéré {
  background: #fff3cd;
  color: #856404;
}

.status-badge.sévère {
  background: #f8d7da;
  color: #721c24;
}

/* Navbar & Container */
.navbar {
  background: #2c3e50;
  color: white;
  padding: 1rem 2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.nav-links {
  list-style: none;
  display: flex;
  gap: 20px;
}

.nav-links li {
  cursor: pointer;
  opacity: 0.7;
  font-weight: bold;
}

.nav-links li.active {
  opacity: 1;
  border-bottom: 2px solid white;
}

.content {
  padding: 2rem;
  max-width: 1200px;
  margin: auto;
}

/* Global Cards */
.card {
  background: white;
  padding: 1.5rem;
  border-radius: 12px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
  margin-bottom: 2rem;
}

.section-title {
  margin-bottom: 1.5rem;
  color: #34495e;
  font-size: 1.2rem;
  border-left: 4px solid #3498db;
  padding-left: 10px;
}

/* Questions List */
.question-item {
  display: flex;
  justify-content: space-between;
  background: white;
  padding: 1rem;
  border-radius: 8px;
  margin-bottom: 10px;
  border: 1px solid #eee;
}

.q-number {
  font-weight: bold;
  color: #3498db;
  margin-right: 15px;
}

/* Consultation Layout */
.consult-layout {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 30px;
  align-items: start;
}

/* Form Styles */
.modern-form .field {
  margin-bottom: 1.2rem;
}

.modern-form label {
  display: block;
  margin-bottom: 5px;
  font-weight: bold;
  font-size: 0.9rem;
  color: #7f8c8d;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 15px;
}

input,
select {
  width: 100%;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 8px;
  font-size: 1rem;
}

/* Gravity Selector */
.gravity-selector {
  display: flex;
  gap: 10px;
}

.radio-label {
  flex: 1;
  text-align: center;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 8px;
  cursor: pointer;
  font-weight: bold;
  transition: 0.3s;
}

.radio-label input {
  display: none;
}

.radio-label.active {
  background: #3498db;
  color: white;
  border-color: #3498db;
}

.radio-label.warning.active {
  background: #f39c12;
  border-color: #f39c12;
}

.radio-label.danger.active {
  background: #e74c3c;
  border-color: #e74c3c;
}

.multi-select {
  height: 80px;
}

.btn-primary {
  width: 100%;
  padding: 12px;
  background: #27ae60;
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: bold;
  cursor: pointer;
}

/* Consult Cards */
.consult-card {
  background: white;
  padding: 1rem;
  border-radius: 10px;
  border: 1px solid #eee;
  margin-bottom: 15px;
  transition: 0.2s;
}

.consult-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
}

.card-header {
  display: flex;
  justify-content: space-between;
  margin-bottom: 8px;
}

.patient-name {
  font-weight: bold;
  font-size: 1.1rem;
}

.status-badge {
  padding: 4px 10px;
  border-radius: 20px;
  font-size: 0.75rem;
  text-transform: uppercase;
  font-weight: bold;
}

.status-badge.normal {
  background: #d4edda;
  color: #155724;
}

.status-badge.modéré {
  background: #fff3cd;
  color: #856404;
}

.status-badge.sévère {
  background: #f8d7da;
  color: #721c24;
}

.card-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 5px;
  margin-top: 10px;
}

.tag {
  font-size: 0.75rem;
  padding: 3px 8px;
  border-radius: 4px;
  border: 1px solid #ddd;
}

.tag.tox {
  background: #fcf3cf;
}

.tag.sym {
  background: #ebf5fb;
}

/* Utils */
.btn-save,
.btn-edit {
  background: #3498db;
  color: white;
  border: none;
  padding: 8px 15px;
  border-radius: 5px;
  cursor: pointer;
}

.btn-del {
  background: #e74c3c;
  color: white;
  border: none;
  padding: 8px 15px;
  border-radius: 5px;
  margin-left: 5px;
  cursor: pointer;
}

.btn-edit-sm {
  background: #3498db;
  color: white;
  border: none;
  padding: 8px 15px;
  border-radius: 5px;
  cursor: pointer;
}

.btn-del-sm {
  background: #e74c3c;
  color: white;
  border: none;
  padding: 8px 15px;
  border-radius: 5px;
  margin-left: 5px;
  cursor: pointer;
}

.alert {
  background: #d4edda;
  color: #155724;
  padding: 1rem;
  border-radius: 8px;
  margin-bottom: 1rem;
}

.modal-content {
  width: 90%;
  max-width: 600px;
  max-height: 90vh;
  overflow-y: auto;
  background: white;
  padding: 2rem;
  border-radius: 15px;
}

.decision-box {
  border: 1px solid #eee;
  padding: 10px;
  border-radius: 8px;
  margin-bottom: 10px;
  background: #f9f9f9;
}

.sub-field {
  margin-top: 10px;
  padding-left: 20px;
  border-left: 2px solid #3498db;
}

.sub-field input,
.sub-field textarea {
  margin-bottom: 5px;
  width: 100%;
  padding: 8px;
}

.btn-action-etat {
  background: #3498db;
  color: white;
  border: none;
  padding: 5px 10px;
  border-radius: 5px;
  cursor: pointer;
  margin-right: 5px;
}

.modal-actions {
  display: flex;
  gap: 10px;
  margin-top: 20px;
}

textarea {
  width: 100%;
  height: 80px;
  border-radius: 8px;
  border: 1px solid #ddd;
  padding: 10px;
}

.badge-done {
  background: #e8f5e9;
  color: #2e7d32;
  font-size: 0.75rem;
  padding: 4px 8px;
  border-radius: 4px;
  font-weight: bold;
  margin-right: 5px;
}
.etat-actions {
  display: inline-flex;
  gap: 5px;
}
.btn-edit-sm { background: #f39c12; color: white; border: none; padding: 3px 7px; border-radius: 4px; cursor: pointer; }
.btn-del-sm { background: #e74c3c; color: white; border: none; padding: 3px 7px; border-radius: 4px; cursor: pointer; }
.divider { margin: 0 10px; color: #ccc; }
</style>
