
<template>
  <div class="consult-page">
    <div v-if="message" class="alert">{{ message }}</div>

    <div class="consult-layout">
      <aside class="form-section card">
        <h3 class="title-border">{{ isEditingC ? 'Modifier' : 'Nouvelle' }} Consultation</h3>
        <form @submit.prevent="saveConsultation" class="modern-form">
          <div class="field">
            <label>Patient</label>
            <select v-model="formConsultation.patient_id" :disabled="isEditingC" required>
              <option value="">Sélectionner un patient</option>
              <option v-for="p in patients" :key="p.id" :value="p.id">
                {{ p.user?.nom }} {{ p.user?.prenom }}
              </option>
            </select>
          </div>

          <div class="field">
            <label>Date et Heure</label>
            <input type="datetime-local" v-model="formConsultation.date" required>
          </div>

          <div class="field">
            <label>Niveau de Gravité</label>
            <div class="gravity-selector">
              <label v-for="g in ['Normal', 'Modéré', 'Sévère']" :key="g"
                :class="['radio-btn', g.toLowerCase(), { active: formConsultation.gravite === g }]">
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

          <div class="form-actions">
            <button type="submit" class="btn-submit">
              {{ isEditingC ? 'Enregistrer les modifications' : 'Créer Consultation' }}
            </button>
            <button v-if="isEditingC" @click="resetConsultForm" type="button" class="btn-cancel">Annuler</button>
          </div>
        </form>
      </aside>

      <section class="list-section">
        <h3 class="title-border">Historique des consultations</h3>
        <div class="consult-cards-container">
          <div v-for="c in consultations" :key="c.id" class="consult-card">
            <div class="card-main">
              <div class="patient-info">
                <strong>{{ c.patient?.user?.nom }} {{ c.patient?.user?.prenom }}</strong>
                <span :class="['gravite-badge', c.gravite?.toLowerCase()]">{{ c.gravite }}</span>
              </div>
              <p class="date">📅 {{ new Date(c.date).toLocaleString() }}</p>

              <div class="tags">
                <span v-for="t in c.toxicites" :key="t.id" class="tag tox">{{ t.nom }}</span>
                <span v-for="s in c.symptomes" :key="s.id" class="tag sym">{{ s.nom }}</span>
              </div>
            </div>

            <div class="card-footer">
              <div class="main-actions">
                <button @click="prepareEditConsult(c)" class="btn-edit" title="Modifier">✏️</button>
                <button @click="deleteConsultation(c.id)" class="btn-del" title="Supprimer">🗑️</button>
              </div>

              <div class="etat-section">
                <button v-if="!c.etat_general" @click="openEtatForm(c)" class="btn-add-etat">+ Bilan & Décisions</button>
                <div v-else class="etat-exists">
                  <span class="badge-done">✅ Bilan prêt</span>
                  <button @click="prepareEditEtat(c)" class="btn-small">📝</button>
                  <button @click="deleteEtatGeneral(c.etat_general.id)" class="btn-small del">✖</button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>

    <div v-if="showEtatModal" class="modal-overlay">
      <div class="modal-card card">
        <h3>{{ isEditingEtat ? 'Modifier' : 'Ajouter' }} Bilan Médical</h3>
        <p>Patient: <strong>{{ selectedConsultation?.patient?.user?.nom }}</strong></p>

        <div class="field">
          <label>Observations :</label>
          <textarea v-model="formEtat.description" placeholder="Observations cliniques..." required></textarea>
        </div>

        <div class="decisions-container">
          <h4>Décisions Médicales</h4>

          <div class="decision-item">
            <label><input type="checkbox" v-model="formEtat.hasRDV"> Nouveau Rendez-vous</label>
            <input v-if="formEtat.hasRDV" type="datetime-local" v-model="formEtat.rdv_date" class="mt-5">
          </div>

          <div class="decision-item">
            <label><input type="checkbox" v-model="formEtat.hasTraitement"> Prescrire Traitement</label>
            <div v-if="formEtat.hasTraitement" class="sub-form">
              <input v-model="formEtat.tr_nom" placeholder="Nom du médicament" class="mt-5">
              <textarea v-model="formEtat.tr_desc" placeholder="Posologie..." class="mt-5"></textarea>
            </div>
          </div>

          <div class="decision-item">
            <label><input type="checkbox" v-model="formEtat.hasConseil"> Donner un Conseil</label>
            <textarea v-if="formEtat.hasConseil" v-model="formEtat.cons_desc" placeholder="Conseils au patient..." class="mt-5"></textarea>
          </div>
        </div>

        <div class="modal-btns">
          <button @click="saveEtatTotal" class="btn-submit">Valider tout</button>
          <button @click="showEtatModal = false" class="btn-cancel">Annuler</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const API_BASE = 'http://localhost:8080/api/medecin';
const token = localStorage.getItem('user_token');
const message = ref('');

// --- DATA ---
const consultations = ref([]);
const patients = ref([]);
const toxicitesList = ref([]);
const symptomesList = ref([]);

// --- FORM STATE ---
const isEditingC = ref(false);
const editIdC = ref(null);
const formConsultation = ref({
  patient_id: '', date: '', gravite: 'Normal', toxicite_ids: [], symptome_ids: []
});

const showEtatModal = ref(false);
const selectedConsultation = ref(null);
const isEditingEtat = ref(false);
const editEtatId = ref(null);

const formEtat = ref({
  description: '',
  consultation_id: '',
  hasRDV: false, rdv_date: '',
  hasTraitement: false, tr_nom: '', tr_desc: '',
  hasConseil: false, cons_desc: '',
  rondezVous: null, traitement: null, conseil: null
});

// --- FETCH DATA ---
const fetchData = async () => {
  const headers = { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' };
  try {
    const [resC, resP, resT, resS] = await Promise.all([
      fetch(`${API_BASE}/consultations`, { headers }),
      fetch(`${API_BASE}/patients`, { headers }),
      fetch(`${API_BASE}/toxicites`, { headers }),
      fetch(`${API_BASE}/symptomes`, { headers })
    ]);
    if (resC.ok) consultations.value = await resC.json();
    if (resP.ok) patients.value = await resP.json();
    if (resT.ok) toxicitesList.value = await resT.json();
    if (resS.ok) symptomesList.value = await resS.json();
  } catch (e) { console.error(e); }
};

// --- ACTIONS CONSULTATION ---
const saveConsultation = async () => {
  const method = isEditingC.value ? 'PUT' : 'POST';
  const url = isEditingC.value ? `${API_BASE}/consultations/${editIdC.value}` : `${API_BASE}/consultations`;
  const res = await fetch(url, {
    method,
    headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' },
    body: JSON.stringify(formConsultation.value)
  });
  if (res.ok) {
    message.value = "Opération réussie !";
    resetConsultForm();
    fetchData();
    setTimeout(() => message.value = '', 3000);
  }
};

const prepareEditConsult = (c) => {
  isEditingC.value = true;
  editIdC.value = c.id;
  formConsultation.value = {
    patient_id: c.patient_id,
    date: c.date.replace(' ', 'T'),
    gravite: c.gravite,
    toxicite_ids: c.toxicites?.map(t => t.id) || [],
    symptome_ids: c.symptomes?.map(s => s.id) || []
  };
};

const resetConsultForm = () => {
  isEditingC.value = false;
  editIdC.value = null;
  formConsultation.value = { patient_id: '', date: '', gravite: 'Normal', toxicite_ids: [], symptome_ids: [] };
};

const deleteConsultation = async (id) => {
  if (!confirm("Supprimer ?")) return;
  await fetch(`${API_BASE}/consultations/${id}`, { method: 'DELETE', headers: { 'Authorization': `Bearer ${token}` } });
  fetchData();
};

// --- ACTIONS ETAT & DECISIONS (LOGIQUE MISE À JOUR) ---
const openEtatForm = (consult) => {
  selectedConsultation.value = consult;
  isEditingEtat.value = false;
  formEtat.value = {
    description: '', consultation_id: consult.id,
    hasRDV: false, rdv_date: '',
    hasTraitement: false, tr_nom: '', tr_desc: '',
    hasConseil: false, cons_desc: '',
    rondezVous: null, traitement: null, conseil: null
  };
  showEtatModal.value = true;
};

const prepareEditEtat = (consult) => {
  isEditingEtat.value = true;
  selectedConsultation.value = consult;
  const eg = consult.etat_general;
  editEtatId.value = eg.id;
  console.log("Etat General à éditer:", eg);
  formEtat.value = {
    description: eg.description,
    consultation_id: consult.id,
    // Mapping des décisions existantes
    hasRDV: !!eg.rendez_vous,
    rdv_date: eg.rendez_vous ? eg.rendez_vous.date.replace(' ', 'T') : '',
    rondezVous: eg.rendez_vous || null,
    patient_id: consult.patient_id,
    hasTraitement: !!eg.traitement,
    tr_nom: eg.traitement ? eg.traitement.nom : '',
    tr_desc: eg.traitement ? eg.traitement.description : '',
    traitement: eg.traitement || null,
    hasConseil: !!eg.conseil,
    cons_desc: eg.conseil ? eg.conseil.description : '',
    conseil: eg.conseil || null
  };
  showEtatModal.value = true;
};

const saveEtatTotal = async () => {
  const method = isEditingEtat.value ? 'PUT' : 'POST';
  const urlEtat = isEditingEtat.value ? `${API_BASE}/etat-general/${editEtatId.value}` : `${API_BASE}/etat-general`;

  try {
    const resEtat = await fetch(urlEtat, {
      method,
      headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' },
      body: JSON.stringify({ description: formEtat.value.description, consultation_id: formEtat.value.consultation_id })
    });

    if (!resEtat.ok) throw new Error("Erreur Bilan");
    const etatData = await resEtat.json();
    const egId = isEditingEtat.value ? editEtatId.value : etatData.data.id;

    // Helper function pour gérer POST ou PUT pour chaque décision
    const handleDecision = async (hasType, data, existingObj, endpoint) => {
      if (hasType) {
        const dMethod = (isEditingEtat.value && existingObj) ? 'PUT' : 'POST';
        const dUrl = (isEditingEtat.value && existingObj) ? `${API_BASE}/${endpoint}/${existingObj.id}` : `${API_BASE}/${endpoint}`;
        console.log('json body = ', { ...data, etat_general_id: egId });

        await fetch(dUrl, {
          method: dMethod,
          headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' },
          body: JSON.stringify({ ...data, etat_general_id: egId })
        });
      }
    };

    await Promise.all([
      handleDecision(formEtat.value.hasRDV, { date: formEtat.value.rdv_date , patient_id: formEtat.value.patient_id}, formEtat.value.rondezVous, 'rendez-vous'),
      handleDecision(formEtat.value.hasTraitement, { nom: formEtat.value.tr_nom, description: formEtat.value.tr_desc }, formEtat.value.traitement, 'traitements'),
      handleDecision(formEtat.value.hasConseil, { description: formEtat.value.cons_desc }, formEtat.value.conseil, 'conseils')
    ]);

    message.value = "Bilan mis à jour !";
    showEtatModal.value = false;
    fetchData();
  } catch (e) { console.error(e); }
};

const deleteEtatGeneral = async (id) => {
  if (!confirm("Supprimer bilan ?")) return;
  await fetch(`${API_BASE}/etat-general/${id}`, { method: 'DELETE', headers: { 'Authorization': `Bearer ${token}` } });
  fetchData();
};

onMounted(() => fetchData());
</script>

<style scoped>
/* Conserver tes styles CSS d'origine ici */
.consult-page { padding: 20px; font-family: sans-serif; }
.consult-layout { display: grid; grid-template-columns: 380px 1fr; gap: 2rem; }
.card { background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
.title-border { border-left: 4px solid #3b82f6; padding-left: 10px; margin-bottom: 1.5rem; }
.alert { background: #dcfce7; color: #166534; padding: 10px; border-radius: 8px; margin-bottom: 1rem; text-align: center; }
.field { margin-bottom: 1.2rem; }
label { display: block; font-size: 0.85rem; font-weight: 600; color: #64748b; margin-bottom: 5px; }
input, select, textarea { width: 100%; padding: 10px; border: 1px solid #e2e8f0; border-radius: 8px; }
.gravity-selector { display: flex; gap: 8px; }
.radio-btn { flex: 1; text-align: center; padding: 8px; border: 1px solid #e2e8f0; border-radius: 8px; cursor: pointer; }
.radio-btn.active.normal { background: #dcfce7; }
.radio-btn.active.modéré { background: #fef9c3; }
.radio-btn.active.sévère { background: #fee2e2; }
.consult-cards-container { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1.5rem; }
.consult-card { border: 1px solid #e2e8f0; border-radius: 12px; }
.card-footer { padding: 1rem; background: #f8fafc; display: flex; justify-content: space-between; align-items: center; }
.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 100; }
.modal-card { width: 500px; max-height: 90vh; overflow-y: auto; }
.decisions-container { margin: 15px 0; padding: 15px; background: #f1f5f9; border-radius: 8px; }
.decision-item { margin-bottom: 12px; padding: 10px; background: white; border-radius: 6px; }
.btn-submit { background: #3b82f6; color: white; border: none; padding: 10px; border-radius: 8px; cursor: pointer; width: 100%; }
.btn-cancel { background: #94a3b8; color: white; border: none; padding: 10px; border-radius: 8px; cursor: pointer; width: 100%; margin-top: 5px; }
.badge-done { color: #10b981; font-weight: bold; }
.btn-small { border: none; padding: 4px 8px; border-radius: 4px; cursor: pointer; margin-left: 5px; }
</style>

