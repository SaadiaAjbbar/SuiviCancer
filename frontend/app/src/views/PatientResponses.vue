<script setup>
import { ref, onMounted } from 'vue';
import AppCard from '@/components/ui/AppCard.vue';
import AppButton from '@/components/ui/AppButton.vue';

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
    const egId = isEditingEtat.value ? editEtatId.value : (etatData.data?.id || etatData.id);

    if (!egId) {
      console.error("Impossible de récupérer l'ID de l'état général", etatData);
      return;
    }

    const handleDecision = async (hasType, data, existingObj, endpoint) => {
      if (hasType) {
        const dMethod = (isEditingEtat.value && existingObj) ? 'PUT' : 'POST';
        const dUrl = (isEditingEtat.value && existingObj) ? `${API_BASE}/${endpoint}/${existingObj.id}` : `${API_BASE}/${endpoint}`;
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

<template>
  <div class="space-y-10 animate-in fade-in duration-500 pb-20">
    <div v-if="message" class="fixed top-24 right-8 z-50 p-4 bg-emerald-500 text-white rounded-2xl shadow-xl animate-in slide-in-from-right-10 font-bold">
      {{ message }}
    </div>

    <div class="flex flex-col gap-2">
      <h1 class="text-3xl font-black text-slate-900 tracking-tight">Réponses des Patients</h1>
      <p class="text-slate-500 font-medium">Consultez les questionnaires remplis par vos patients et créez des bilans médicaux.</p>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
      <AppCard v-for="rep in responses" :key="rep.id" padding="false" class="flex flex-col border-slate-200/60 shadow-sm hover:shadow-md transition-all">
        <div class="p-6 border-b border-slate-50 bg-slate-50/30">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
              <div class="w-12 h-12 bg-primary text-white flex items-center justify-center rounded-2xl font-bold shadow-lg shadow-primary/20">
                {{ rep.patient?.user?.nom?.charAt(0) }}{{ rep.patient?.user?.prenom?.charAt(0) }}
              </div>
              <div>
                <h4 class="font-black text-slate-900 leading-none">{{ rep.patient?.user?.nom }} {{ rep.patient?.user?.prenom }}</h4>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">
                  Le {{ new Date(rep.created_at).toLocaleString('fr-FR') }}
                </p>
              </div>
            </div>
            <div v-if="rep.etat_general" class="px-3 py-1 bg-emerald-50 text-emerald-600 text-[10px] font-black uppercase tracking-widest rounded-full border border-emerald-100">
              Traité
            </div>
            <div v-else class="px-3 py-1 bg-amber-50 text-amber-600 text-[10px] font-black uppercase tracking-widest rounded-full border border-amber-100 animate-pulse">
              En attente
            </div>
          </div>
        </div>

        <div class="p-6 flex-1 space-y-4">
          <h5 class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Questionnaire du jour</h5>
          <div class="space-y-4">
            <div v-for="item in rep.reponse_questionnaires" :key="item.id" class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
              <p class="text-xs font-bold text-slate-500 mb-2">Q: {{ item.question?.titre }}</p>
              <p class="text-sm font-semibold text-slate-800 leading-relaxed italic">"{{ item.reponse }}"</p>
            </div>
          </div>
        </div>

        <div class="p-6 bg-slate-50/50 border-t border-slate-100">
          <div v-if="!rep.etat_general">
            <AppButton variant="primary" custom-class="w-full shadow-lg shadow-primary/10" @click="openEtatForm(rep)">
              + Créer Bilan Médical
            </AppButton>
          </div>

          <div v-else class="space-y-4">
            <div class="p-4 bg-emerald-50 rounded-2xl border border-emerald-100">
              <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-black text-emerald-700 uppercase tracking-widest flex items-center gap-2">
                  <span class="text-base">✅</span> Bilan Effectué
                </span>
                <div class="flex gap-1">
                  <button @click="prepareEditEtat(rep)" class="p-2 hover:bg-white rounded-lg text-emerald-600 transition-colors" title="Modifier">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" /></svg>
                  </button>
                  <button @click="deleteEtatGeneral(rep.etat_general.id)" class="p-2 hover:bg-white rounded-lg text-red-500 transition-colors" title="Supprimer">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                  </button>
                </div>
              </div>
              <div class="space-y-1">
                <p v-if="rep.etat_general.rendez_vous" class="text-[10px] font-bold text-emerald-800/60 uppercase tracking-tighter">📅 RDV: {{ new Date(rep.etat_general.rendez_vous.date).toLocaleDateString() }}</p>
                <p v-if="rep.etat_general.traitement" class="text-[10px] font-bold text-emerald-800/60 uppercase tracking-tighter">💊 Tr: {{ rep.etat_general.traitement.nom }}</p>
                <p v-if="rep.etat_general.conseil" class="text-[10px] font-bold text-emerald-800/60 uppercase tracking-tighter">💡 Conseil enregistré</p>
              </div>
            </div>
          </div>
        </div>
      </AppCard>

      <div v-if="responses.length === 0" class="col-span-full py-24 text-center bg-white rounded-3xl border border-dashed border-slate-300">
        <div class="text-7xl mb-6">🏝️</div>
        <h3 class="text-2xl font-black text-slate-900">Tout est calme ici</h3>
        <p class="text-slate-500 font-medium max-w-md mx-auto mt-2">Aucune nouvelle réponse n'a été reçue pour le moment. Revenez un peu plus tard !</p>
      </div>
    </div>

    <!-- Modal (Shared with Consultations) -->
    <div v-if="showEtatModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm animate-in fade-in duration-300">
      <div class="w-full max-w-lg bg-white rounded-3xl shadow-2xl overflow-hidden animate-in zoom-in-95 duration-300">
        <div class="p-8 border-b border-slate-100 flex justify-between items-center">
          <div>
            <h3 class="text-2xl font-black text-slate-900">{{ isEditingEtat ? 'Modifier' : 'Ajouter' }} Bilan</h3>
            <p class="text-slate-500 font-medium">Patient: <span class="text-primary font-bold">{{ selectedResponse?.patient?.user?.nom }} {{ selectedResponse?.patient?.user?.prenom }}</span></p>
          </div>
          <button @click="showEtatModal = false" class="text-slate-400 hover:text-slate-600 text-2xl">×</button>
        </div>

        <div class="p-8 max-h-[70vh] overflow-y-auto space-y-8">
          <div>
            <label class="block text-sm font-bold text-slate-700 mb-2 text-center uppercase tracking-widest">Observations Cliniques</label>
            <textarea
              v-model="formEtat.description"
              placeholder="Décrivez l'état de santé général..."
              class="w-full p-4 bg-slate-50 border border-slate-200 rounded-2xl h-32 focus:bg-white focus:ring-4 focus:ring-primary/10 outline-none transition-all resize-none"
            ></textarea>
          </div>

          <div class="space-y-4">
            <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest border-b border-slate-100 pb-2">Décisions Médicales</h4>
            
            <div class="p-4 rounded-2xl border border-slate-100 bg-slate-50 space-y-4">
              <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" v-model="formEtat.hasRDV" class="w-5 h-5 rounded text-primary border-slate-300 focus:ring-primary">
                <span class="font-bold text-slate-700">Nouveau Rendez-vous</span>
              </label>
              <input v-if="formEtat.hasRDV" type="datetime-local" v-model="formEtat.rdv_date" class="w-full p-3 bg-white border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-primary/20 transition-all">
            </div>

            <div class="p-4 rounded-2xl border border-slate-100 bg-slate-50 space-y-4">
              <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" v-model="formEtat.hasTraitement" class="w-5 h-5 rounded text-primary border-slate-300 focus:ring-primary">
                <span class="font-bold text-slate-700">Prescrire Traitement</span>
              </label>
              <div v-if="formEtat.hasTraitement" class="space-y-3">
                <input v-model="formEtat.tr_nom" placeholder="Nom du médicament" class="w-full p-3 bg-white border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-primary/20 transition-all">
                <textarea v-model="formEtat.tr_desc" placeholder="Posologie et durée..." class="w-full p-3 bg-white border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-primary/20 transition-all h-24 resize-none"></textarea>
              </div>
            </div>

            <div class="p-4 rounded-2xl border border-slate-100 bg-slate-50 space-y-4">
              <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" v-model="formEtat.hasConseil" class="w-5 h-5 rounded text-primary border-slate-300 focus:ring-primary">
                <span class="font-bold text-slate-700">Donner un Conseil</span>
              </label>
              <textarea v-if="formEtat.hasConseil" v-model="formEtat.cons_desc" placeholder="Conseils hygiéno-diététiques..." class="w-full p-3 bg-white border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-primary/20 transition-all h-24 resize-none"></textarea>
            </div>
          </div>
        </div>

        <div class="p-8 bg-slate-50 border-t border-slate-100 flex gap-4">
          <AppButton @click="saveEtatTotal" custom-class="flex-1 shadow-lg shadow-primary/20">Valider tout</AppButton>
          <AppButton variant="secondary" @click="showEtatModal = false" custom-class="flex-1">Annuler</AppButton>
        </div>
      </div>
    </div>
  </div>
</template>
