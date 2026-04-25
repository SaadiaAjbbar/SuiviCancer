<template>
  <div class="responses-page">
    <div v-if="message" class="alert">{{ message }}</div>

    <header class="section-header">
      <h3 class="title-border">Réponses des Questionnaires Patients</h3>
    </header>

    <div class="responses-grid">
      <div v-for="rep in responses" :key="rep.id" class="response-card card">
        <div class="card-header">
          <div class="patient-meta">
            <div class="avatar-sm">{{ rep.patient?.user?.nom?.charAt(0) }}</div>
            <div>
              <strong>{{ rep.patient?.user?.nom }} {{ rep.patient?.user?.prenom }}</strong>
              <p class="text-muted">Le {{ new Date(rep.created_at).toLocaleString() }}</p>
            </div>
          </div>
        </div>

        <div class="card-body">
          <ul class="qa-list">
            <li v-for="item in rep.reponse_questionnaires" :key="item.id">
              <span class="question">Q: {{ item.question?.titre }}</span>
              <span class="answer">R: {{ item.reponse }}</span>
            </li>
          </ul>
        </div>

        <div class="card-footer">
          <button v-if="!rep.etat_general" @click="openEtatForm(rep)" class="btn-add-etat">
            + Créer Bilan Médical
          </button>

          <div v-else class="etat-exists">
            <div class="etat-summary">
              <span class="badge-done">✅ Bilan Effectué</span>
              <div class="mini-details">
                <p v-if="rep.etat_general.rendez_vous" class="detail-item">📅 RDV: {{ new Date(rep.etat_general.rendez_vous.date).toLocaleDateString() }}</p>
                <p v-if="rep.etat_general.traitement" class="detail-item">💊 Tr: {{ rep.etat_general.traitement.nom }}</p>
                <p v-if="rep.etat_general.conseil" class="detail-item">💡 Conseil enregistré</p>
              </div>
            </div>
            <div class="etat-actions">
              <button @click="prepareEditEtat(rep)" class="btn-small" title="Modifier le bilan">📝</button>
              <button @click="deleteEtatGeneral(rep.etat_general.id)" class="btn-small del" title="Supprimer le bilan">✖</button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-if="showEtatModal" class="modal-overlay">
      <div class="modal-card card">
        <h3>{{ isEditingEtat ? 'Modifier' : 'Ajouter' }} Bilan Médical</h3>
        <p>Patient: <strong>{{ selectedResponse?.patient?.user?.nom }} {{ selectedResponse?.patient?.user?.prenom }}</strong></p>

        <div class="field">
          <label>Observations (Etat Général) :</label>
          <textarea v-model="formEtat.description" placeholder="Observations médicales..." required></textarea>
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

const responses = ref([]);
const showEtatModal = ref(false);
const selectedResponse = ref(null);
const isEditingEtat = ref(false);
const editEtatId = ref(null);

const formEtat = ref({
  description: '',
  reponse_id: '',
  hasRDV: false, rdv_date: '',
  hasTraitement: false, tr_nom: '', tr_desc: '',
  hasConseil: false, cons_desc: '',
  rondezVous: null, traitement: null, conseil: null
});

const fetchResponses = async () => {
  try {
    const res = await fetch(`${API_BASE}/responses`, {
      headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
    });
    if (res.ok) responses.value = await res.json();
  } catch (e) { console.error("Fetch error:", e); }
};

const openEtatForm = (rep) => {
  selectedResponse.value = rep;
  isEditingEtat.value = false;
  formEtat.value = {
    description: '', reponse_id: rep.id,
    hasRDV: false, rdv_date: '',
    hasTraitement: false, tr_nom: '', tr_desc: '',
    hasConseil: false, cons_desc: '',
    rondezVous: null, traitement: null, conseil: null
  };
  showEtatModal.value = true;
};

const prepareEditEtat = (rep) => {
  isEditingEtat.value = true;
  selectedResponse.value = rep;
  const eg = rep.etat_general;
  editEtatId.value = eg.id;

  formEtat.value = {
    description: eg.description,
    reponse_id: rep.id,
    hasRDV: !!eg.rendez_vous,
    rdv_date: eg.rendez_vous ? eg.rendez_vous.date.replace(' ', 'T') : '',
    rondezVous: eg.rendez_vous || null,
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
      body: JSON.stringify({
        description: formEtat.value.description,
        reponse_id: formEtat.value.reponse_id
      })
    });

    if (!resEtat.ok) throw new Error("Erreur Bilan");

    const etatData = await resEtat.json();

    // Correction cruciale pour l'ID : on vérifie toutes les structures possibles
    const egId = isEditingEtat.value ? editEtatId.value : (etatData.data?.id || etatData.id);

    if (!egId) {
      console.error("Impossible de récupérer l'ID de l'état général", etatData);
      return;
    }

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
      handleDecision(formEtat.value.hasRDV, { date: formEtat.value.rdv_date , patient_id: selectedResponse.value.patient?.id }, formEtat.value.rondezVous, 'rendez-vous'),
      handleDecision(formEtat.value.hasTraitement, { nom: formEtat.value.tr_nom, description: formEtat.value.tr_desc }, formEtat.value.traitement, 'traitements'),
      handleDecision(formEtat.value.hasConseil, { description: formEtat.value.cons_desc }, formEtat.value.conseil, 'conseils')
    ]);

    message.value = "Bilan mis à jour !";
    showEtatModal.value = false;
    fetchResponses();
    setTimeout(() => message.value = '', 3000);
  } catch (e) {
    console.error("Erreur lors de la sauvegarde complète:", e);
  }
};

const deleteEtatGeneral = async (id) => {
  if (!confirm("Supprimer bilan ?")) return;
  await fetch(`${API_BASE}/etat-general/${id}`, { method: 'DELETE', headers: { 'Authorization': `Bearer ${token}` } });
  fetchResponses();
};

onMounted(() => fetchResponses());
</script>

<style scoped>
.responses-page { padding: 20px; font-family: 'Inter', sans-serif; }
.section-header { margin-bottom: 2rem; }
.title-border { border-left: 4px solid #3b82f6; padding-left: 10px; }
.alert { background: #dcfce7; color: #166534; padding: 10px; border-radius: 8px; margin-bottom: 1rem; text-align: center; }

.responses-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 1.5rem; }
.card { background: white; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); border: 1px solid #e2e8f0; display: flex; flex-direction: column; overflow: hidden; }

.card-header { padding: 1rem; border-bottom: 1px solid #f1f5f9; }
.patient-meta { display: flex; gap: 12px; align-items: center; }
.avatar-sm { width: 40px; height: 40px; background: #3b82f6; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; }
.text-muted { font-size: 0.8rem; color: #64748b; margin: 0; }

.card-body { padding: 1rem; flex-grow: 1; }
.qa-list { list-style: none; padding: 0; margin: 0; }
.qa-list li { margin-bottom: 12px; padding: 10px; background: #f8fafc; border-radius: 8px; border-left: 3px solid #e2e8f0; }
.question { display: block; font-weight: 600; color: #1e293b; font-size: 0.85rem; margin-bottom: 4px; }
.answer { display: block; color: #3b82f6; font-style: italic; font-size: 0.9rem; }

.card-footer { padding: 1rem; background: #f8fafc; border-top: 1px solid #e2e8f0; }

.etat-exists { display: flex; flex-direction: column; gap: 8px; }
.etat-summary { background: #f0fdf4; padding: 10px; border-radius: 8px; border: 1px solid #dcfce7; }
.badge-done { color: #166534; font-weight: bold; font-size: 0.85rem; display: block; margin-bottom: 5px; }
.mini-details { font-size: 0.75rem; color: #475569; }
.detail-item { margin: 2px 0; padding-bottom: 2px; border-bottom: 1px dashed #d1fae5; }

.etat-actions { display: flex; justify-content: flex-end; gap: 8px; }
.btn-small { padding: 4px 8px; border-radius: 4px; border: none; cursor: pointer; background: white; border: 1px solid #e2e8f0; }
.btn-small.del { color: #ef4444; }

.btn-add-etat { width: 100%; background: #10b981; color: white; border: none; padding: 10px; border-radius: 8px; font-weight: bold; cursor: pointer; transition: 0.3s; }
.btn-add-etat:hover { background: #059669; }

.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; backdrop-filter: blur(2px); z-index: 100; }
.modal-card { width: 500px; max-height: 90vh; overflow-y: auto; padding: 1.5rem; }
.field { margin-bottom: 1.2rem; }
.field label { display: block; font-size: 0.85rem; font-weight: 600; color: #64748b; margin-bottom: 5px; }
textarea, input { width: 100%; padding: 10px; border: 1px solid #e2e8f0; border-radius: 8px; }

.decisions-container { background: #f1f5f9; padding: 15px; border-radius: 8px; margin: 15px 0; }
.decision-item { background: white; padding: 10px; border-radius: 6px; margin-bottom: 10px; border: 1px solid #e2e8f0; }

.btn-submit { background: #3b82f6; color: white; padding: 10px; border-radius: 8px; width: 100%; font-weight: bold; border: none; cursor: pointer; }
.btn-cancel { background: #94a3b8; color: white; padding: 10px; border-radius: 8px; width: 100%; border: none; margin-top: 5px; cursor: pointer; }
.mt-5 { margin-top: 5px; }
</style>
