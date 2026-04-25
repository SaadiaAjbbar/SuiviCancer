<script setup>
import { ref, onMounted } from 'vue';
import AppCard from '@/components/ui/AppCard.vue';
import AppButton from '@/components/ui/AppButton.vue';
import AppInput from '@/components/ui/AppInput.vue';

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
      fetch(`${API_BASE}/my-patients`, { headers }),
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

// --- ACTIONS ETAT & DECISIONS ---
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
  formEtat.value = {
    description: eg.description,
    consultation_id: consult.id,
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

<template>
  <div class="space-y-10 animate-in fade-in duration-500 pb-20">
    <div v-if="message" class="fixed top-24 right-8 z-50 p-4 bg-emerald-500 text-white rounded-2xl shadow-xl animate-in slide-in-from-right-10">
      <div class="flex items-center gap-2 font-bold">
        <span>✅</span> {{ message }}
      </div>
    </div>

    <div class="flex flex-col lg:flex-row gap-10">
      <!-- Left: Form -->
      <aside class="lg:w-96 space-y-6">
        <AppCard>
          <template #header>
            <h3 class="text-xl font-bold text-slate-900">{{ isEditingC ? 'Modifier' : 'Nouvelle' }} Consultation</h3>
          </template>

          <form @submit.prevent="saveConsultation" class="space-y-6">
            <div>
              <label class="block text-sm font-bold text-slate-700 mb-2">Patient</label>
              <select v-model="formConsultation.patient_id" :disabled="isEditingC" required class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary/20 outline-none transition-all">
                <option value="">Sélectionner un patient</option>
                <option v-for="p in patients" :key="p.id" :value="p.id">
                  {{ p.user?.nom }} {{ p.user?.prenom }}
                </option>
              </select>
            </div>

            <AppInput 
              type="datetime-local" 
              v-model="formConsultation.date" 
              label="Date et Heure" 
              required 
            />

            <div>
              <label class="block text-sm font-bold text-slate-700 mb-2">Gravité</label>
              <div class="grid grid-cols-3 gap-2">
                <button
                  v-for="g in ['Normal', 'Modéré', 'Sévère']"
                  :key="g"
                  type="button"
                  @click="formConsultation.gravite = g"
                  class="px-3 py-2 text-[10px] font-black rounded-xl border-2 transition-all uppercase tracking-widest"
                  :class="formConsultation.gravite === g ? 'bg-primary border-primary text-white shadow-lg shadow-primary/20' : 'border-slate-100 text-slate-400 hover:border-primary/20 bg-white'"
                >
                  {{ g }}
                </button>
              </div>
            </div>

            <div class="space-y-6">
              <!-- Toxicités Multi-select -->
              <div class="space-y-3">
                <label class="block text-sm font-bold text-slate-700">Toxicités</label>
                <div class="p-3 bg-slate-50 rounded-2xl border border-slate-200 max-h-40 overflow-y-auto space-y-1">
                  <label v-for="t in toxicitesList" :key="t.id" class="flex items-center gap-3 p-2 hover:bg-white rounded-lg cursor-pointer transition-colors group">
                    <input 
                      type="checkbox" 
                      :value="t.id" 
                      v-model="formConsultation.toxicite_ids"
                      class="w-4 h-4 rounded border-slate-300 text-primary focus:ring-primary"
                    >
                    <span class="text-sm font-bold text-slate-600 group-hover:text-slate-900">{{ t.nom }}</span>
                  </label>
                  <div v-if="!toxicitesList.length" class="text-xs text-slate-400 italic p-2 text-center">Aucune toxicité disponible</div>
                </div>
                <div class="flex flex-wrap gap-1.5">
                  <span v-for="id in formConsultation.toxicite_ids" :key="id" class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-primary/10 text-primary rounded-lg text-[10px] font-black uppercase tracking-tight">
                    {{ toxicitesList.find(t => t.id === id)?.nom }}
                    <button type="button" @click="formConsultation.toxicite_ids = formConsultation.toxicite_ids.filter(i => i !== id)" class="hover:text-red-500 transition-colors">×</button>
                  </span>
                </div>
              </div>

              <!-- Symptômes Multi-select -->
              <div class="space-y-3">
                <label class="block text-sm font-bold text-slate-700">Symptômes</label>
                <div class="p-3 bg-slate-50 rounded-2xl border border-slate-200 max-h-40 overflow-y-auto space-y-1">
                  <label v-for="s in symptomesList" :key="s.id" class="flex items-center gap-3 p-2 hover:bg-white rounded-lg cursor-pointer transition-colors group">
                    <input 
                      type="checkbox" 
                      :value="s.id" 
                      v-model="formConsultation.symptome_ids"
                      class="w-4 h-4 rounded border-slate-300 text-primary focus:ring-primary"
                    >
                    <span class="text-sm font-bold text-slate-600 group-hover:text-slate-900">{{ s.nom }}</span>
                  </label>
                  <div v-if="!symptomesList.length" class="text-xs text-slate-400 italic p-2 text-center">Aucun symptôme disponible</div>
                </div>
                <div class="flex flex-wrap gap-1.5">
                  <span v-for="id in formConsultation.symptome_ids" :key="id" class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-blue-50 text-blue-600 rounded-lg text-[10px] font-black uppercase tracking-tight">
                    {{ symptomesList.find(s => s.id === id)?.nom }}
                    <button type="button" @click="formConsultation.symptome_ids = formConsultation.symptome_ids.filter(i => i !== id)" class="hover:text-red-500 transition-colors">×</button>
                  </span>
                </div>
              </div>
            </div>

            <div class="flex flex-col gap-3 pt-4">
              <AppButton type="submit" custom-class="w-full">
                {{ isEditingC ? 'Mettre à jour' : 'Créer Consultation' }}
              </AppButton>
              <AppButton v-if="isEditingC" variant="secondary" @click="resetConsultForm" custom-class="w-full">
                Annuler
              </AppButton>
            </div>
          </form>
        </AppCard>
      </aside>

      <!-- Right: List -->
      <section class="flex-1 space-y-6">
        <h3 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-3">
          <span class="text-slate-300">📅</span> Historique des consultations
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <AppCard v-for="c in consultations" :key="c.id" padding="false" class="flex flex-col">
            <div class="p-6 flex-1">
              <div class="flex justify-between items-start mb-4">
                <div>
                  <h4 class="font-black text-slate-900 text-lg">{{ c.patient?.user?.nom }} {{ c.patient?.user?.prenom }}</h4>
                  <p class="text-xs text-slate-500 font-bold mt-1 tracking-wide">
                    {{ new Date(c.date).toLocaleString('fr-FR', { day: 'numeric', month: 'long', hour: '2-digit', minute: '2-digit' }) }}
                  </p>
                </div>
                <span 
                  class="px-2 py-1 rounded text-[10px] font-black uppercase tracking-widest"
                  :class="{
                    'bg-emerald-50 text-emerald-600': c.gravite === 'Normal',
                    'bg-amber-50 text-amber-600': c.gravite === 'Modéré',
                    'bg-red-50 text-red-600': c.gravite === 'Sévère'
                  }"
                >
                  {{ c.gravite }}
                </span>
              </div>

              <div class="flex flex-wrap gap-2 mt-4">
                <span v-for="t in c.toxicites" :key="t.id" class="px-2 py-1 bg-amber-50 text-amber-700 text-[10px] font-bold rounded-lg border border-amber-100">
                  {{ t.nom }}
                </span>
                <span v-for="s in c.symptomes" :key="s.id" class="px-2 py-1 bg-blue-50 text-blue-700 text-[10px] font-bold rounded-lg border border-blue-100">
                  {{ s.nom }}
                </span>
              </div>
            </div>

            <div class="p-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
              <div class="flex gap-2">
                <button @click="prepareEditConsult(c)" class="p-2 hover:bg-white hover:shadow-sm rounded-lg text-slate-400 hover:text-primary transition-all">✏️</button>
                <button @click="deleteConsultation(c.id)" class="p-2 hover:bg-white hover:shadow-sm rounded-lg text-slate-400 hover:text-red-500 transition-all">🗑️</button>
              </div>

              <div class="flex items-center gap-2">
                <AppButton v-if="!c.etat_general" variant="outline" size="sm" @click="openEtatForm(c)">
                  + Bilan
                </AppButton>
                <div v-else class="flex items-center gap-2">
                  <span class="text-xs font-bold text-emerald-600 bg-emerald-50 px-2 py-1 rounded-full flex items-center gap-1">
                    ✅ Bilan prêt
                  </span>
                  <button @click="prepareEditEtat(c)" class="text-xs hover:underline text-primary font-bold">Modifier</button>
                </div>
              </div>
            </div>
          </AppCard>
        </div>
      </section>
    </div>

    <!-- Modal -->
    <div v-if="showEtatModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm animate-in fade-in duration-300">
      <div class="w-full max-w-lg bg-white rounded-3xl shadow-2xl overflow-hidden animate-in zoom-in-95 duration-300">
        <div class="p-8 border-b border-slate-100 flex justify-between items-center">
          <div>
            <h3 class="text-2xl font-black text-slate-900">{{ isEditingEtat ? 'Modifier' : 'Ajouter' }} Bilan</h3>
            <p class="text-slate-500 font-medium">Patient: <span class="text-primary font-bold">{{ selectedConsultation?.patient?.user?.nom }}</span></p>
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
            
            <!-- RDV -->
            <div class="p-4 rounded-2xl border border-slate-100 bg-slate-50 space-y-4">
              <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" v-model="formEtat.hasRDV" class="w-5 h-5 rounded text-primary border-slate-300 focus:ring-primary">
                <span class="font-bold text-slate-700">Nouveau Rendez-vous</span>
              </label>
              <input v-if="formEtat.hasRDV" type="datetime-local" v-model="formEtat.rdv_date" class="w-full p-3 bg-white border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-primary/20 transition-all">
            </div>

            <!-- Traitement -->
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

            <!-- Conseil -->
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
