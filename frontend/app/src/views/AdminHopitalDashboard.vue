<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import AppCard from '@/components/ui/AppCard.vue';
import AppButton from '@/components/ui/AppButton.vue';
import AppInput from '@/components/ui/AppInput.vue';
import BaseIcon from '@/components/ui/BaseIcon.vue';

const token = localStorage.getItem('user_token');
const currentTab = ref('medecins');
const listData = ref([]);
const isLoading = ref(false);
const messageSuccess = ref('');
const errorMessage = ref('');
const router = useRouter();

// --- FORMULAIRES ---
const formMedecin = ref({ nom: '', prenom: '', email: '', password: '', specialite: '' });
const formInfermier = ref({ nom: '', prenom: '', email: '', mot_de_passe: '' });
const formToxicite = ref({ nom: '', description: '', symptomes: [] });

const isEditing = ref(false);
const editId = ref(null);

const showSymptomeForm = ref(false);
const tempSymptome = ref({ nom: '', description: '' });

const logout = () => {
  localStorage.clear();
  router.push('/login');
};

const fetchData = async (endpoint) => {
  isLoading.value = true;
  let apiPath = endpoint === 'toxicites' ? 'hopital/toxicites' : endpoint;

  try {
    const response = await fetch(`http://localhost:8080/api/${apiPath}`, {
      headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
    });
    const result = await response.json();
    listData.value = Array.isArray(result) ? result : [];
  } catch (e) {
    console.error("Erreur", e);
  } finally {
    isLoading.value = false;
  }
};

const saveMedecin = async () => {
  const method = isEditing.value ? 'PUT' : 'POST';
  const url = isEditing.value ? `http://localhost:8080/api/medecins/${editId.value}` : 'http://localhost:8080/api/medecins';
  
  errorMessage.value = '';
  try {
    const response = await fetch(url, {
      method,
      headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' },
      body: JSON.stringify(formMedecin.value)
    });
    if (response.ok) {
      messageSuccess.value = isEditing.value ? "✅ Médecin mis à jour !" : "✅ Médecin enregistré !";
      resetForm();
      fetchData('medecins');
      setTimeout(() => messageSuccess.value = '', 3000);
    } else {
      const result = await response.json();
      errorMessage.value = result.message || Object.values(result.errors || result || {}).flat().join(' ') || "Erreur lors de l'enregistrement";
    }
  } catch (e) {
    errorMessage.value = "Erreur réseau";
    console.error(e);
  }
};

const saveInfermier = async () => {
  const method = isEditing.value ? 'PUT' : 'POST';
  const url = isEditing.value ? `http://localhost:8080/api/infermiers/${editId.value}` : 'http://localhost:8080/api/infermiers';
  
  errorMessage.value = '';
  try {
    const response = await fetch(url, {
      method,
      headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' },
      body: JSON.stringify(formInfermier.value)
    });
    if (response.ok) {
      messageSuccess.value = isEditing.value ? "✅ Infirmier(e) mis à jour !" : "✅ Infirmier(e) enregistré(e) !";
      resetForm();
      fetchData('infermiers');
      setTimeout(() => messageSuccess.value = '', 3000);
    } else {
      const result = await response.json();
      errorMessage.value = result.message || Object.values(result.errors || result || {}).flat().join(' ') || "Erreur lors de l'enregistrement";
    }
  } catch (e) {
    errorMessage.value = "Erreur réseau";
    console.error(e);
  }
};

const addSymptomeToForm = () => {
  if (tempSymptome.value.nom) {
    formToxicite.value.symptomes.push({ ...tempSymptome.value });
    tempSymptome.value = { nom: '', description: '' };
    showSymptomeForm.value = false;
  }
};

const removeSymptomeFromForm = (index) => {
  formToxicite.value.symptomes.splice(index, 1);
};

const saveToxicite = async () => {
  const method = isEditing.value ? 'PUT' : 'POST';
  const url = isEditing.value ? `http://localhost:8080/api/hopital/toxicites/${editId.value}` : 'http://localhost:8080/api/hopital/toxicites';

  errorMessage.value = '';
  try {
    const response = await fetch(url, {
      method,
      headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' },
      body: JSON.stringify({ nom: formToxicite.value.nom, description: formToxicite.value.description })
    });

    if (response.ok) {
      const result = await response.json();
      const toxiciteId = isEditing.value ? editId.value : (result.data?.id || result.id);

      for (const symp of formToxicite.value.symptomes) {
        if (!symp.id) {
          await fetch('http://localhost:8080/api/symptomes', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${token}` },
            body: JSON.stringify({ ...symp, toxicite_id: toxiciteId })
          });
        }
      }
      messageSuccess.value = isEditing.value ? "✅ Toxicité mise à jour !" : "✅ Toxicité ajoutée !";
      resetForm();
      fetchData('toxicites');
      setTimeout(() => messageSuccess.value = '', 3000);
    } else {
      const result = await response.json();
      errorMessage.value = result.message || "Erreur lors de l'enregistrement de la toxicité";
    }
  } catch (e) {
    errorMessage.value = "Erreur réseau";
    console.error(e);
  }
};

const preparerEditToxicite = (t) => {
  isEditing.value = true;
  editId.value = t.id;
  formToxicite.value = {
    nom: t.nom,
    description: t.description,
    symptomes: t.symptomes ? [...t.symptomes] : []
  };
};

const deleteItem = async (type, id) => {
  if (!confirm(`Voulez-vous supprimer cet élément ?`)) return;
  await fetch(`http://localhost:8080/api/${type}/${id}`, {
    method: 'DELETE',
    headers: { 'Authorization': `Bearer ${token}` }
  });
  fetchData(type);
};

const deleteToxicite = async (id) => {
  if (!confirm("Voulez-vous supprimer cette toxicité ?")) return;
  await fetch(`http://localhost:8080/api/hopital/toxicites/${id}`, {
    method: 'DELETE',
    headers: { 'Authorization': `Bearer ${token}` }
  });
  fetchData('toxicites');
};

const deleteSymptome = async (id) => {
  if (!confirm("Supprimer ce symptôme ?")) return;
  await fetch(`http://localhost:8080/api/symptomes/${id}`, {
    method: 'DELETE',
    headers: { 'Authorization': `Bearer ${token}` }
  });
  if (isEditing.value) {
    formToxicite.value.symptomes = formToxicite.value.symptomes.filter(s => s.id !== id);
  }
  fetchData('toxicites');
};

const resetForm = () => {
  isEditing.value = false;
  editId.value = null;
  formMedecin.value = { nom: '', prenom: '', email: '', password: '', specialite: '' };
  formInfermier.value = { nom: '', prenom: '', email: '', mot_de_passe: '' };
  formToxicite.value = { nom: '', description: '', symptomes: [] };
  showSymptomeForm.value = false;
};

onMounted(() => fetchData('medecins'));
</script>

<template>
  <div class="min-h-screen bg-slate-50 font-sans pb-20">
    <!-- Admin Navbar -->
    <nav class="bg-slate-900 text-white sticky top-0 z-50 shadow-2xl shadow-slate-900/20">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-20">
          <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-primary rounded-2xl flex items-center justify-center shadow-lg shadow-primary/20 border border-primary/20">
              <BaseIcon name="hospital" :size="24" stroke-width="2.5" />
            </div>
            <div>
              <span class="text-xl font-black tracking-tight block leading-none">Admin <span class="text-primary">Hôpital</span></span>
              <span class="text-[10px] font-bold text-slate-500 uppercase tracking-[0.2em] mt-1 block">Panel de Gestion</span>
            </div>
          </div>
          <div class="flex items-center gap-4">
             <button @click="logout" class="group flex items-center gap-2 px-5 py-2.5 bg-red-500/10 hover:bg-red-500 text-red-500 hover:text-white rounded-xl font-black text-[10px] transition-all uppercase tracking-widest border border-red-500/20">
               <BaseIcon name="logout" :size="14" class="group-hover:-translate-x-0.5 transition-transform" />
               Déconnexion
             </button>
          </div>
        </div>
      </div>
      
      <!-- Sub-nav Tabs -->
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 border-t border-white/5">
        <div class="flex gap-4 sm:gap-10">
          <button 
            v-for="tab in [
              { id: 'medecins', name: 'Médecins', icon: 'users' },
              { id: 'infermiers', name: 'Infirmiers', icon: 'users' },
              { id: 'toxicites', name: 'Toxicités', icon: 'stetho' }
            ]" 
            :key="tab.id"
            @click="currentTab = tab.id; resetForm(); fetchData(tab.id === 'toxicites' ? 'toxicites' : tab.id)"
            class="flex items-center gap-2.5 px-2 py-5 text-[10px] font-black uppercase tracking-widest transition-all border-b-2 group"
            :class="currentTab === tab.id ? 'border-primary text-primary' : 'border-transparent text-slate-400 hover:text-slate-200'"
          >
            <BaseIcon :name="tab.icon" :size="14" class="transition-transform group-hover:scale-110" />
            {{ tab.name }}
          </button>
        </div>
      </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
      <div v-if="messageSuccess" class="fixed top-24 right-8 z-50 p-4 bg-emerald-500 text-white rounded-2xl shadow-xl animate-in slide-in-from-right-10 font-bold flex items-center gap-2">
        <span>✅</span> {{ messageSuccess }}
      </div>

      <div v-if="errorMessage" class="fixed top-24 right-8 z-50 p-4 bg-red-500 text-white rounded-2xl shadow-xl animate-in slide-in-from-right-10 font-bold flex items-center gap-2">
        <span>⚠️</span> {{ errorMessage }}
        <button @click="errorMessage = ''" class="ml-2 hover:opacity-70">×</button>
      </div>

      <!-- Content Medecins -->
      <div v-if="currentTab === 'medecins'" class="space-y-10 animate-in fade-in duration-500">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
          <aside class="lg:col-span-1 space-y-6">
            <AppCard>
              <template #header>
                <h3 class="text-lg font-black text-slate-900">{{ isEditing ? 'Modifier' : 'Ajouter' }} Médecin</h3>
              </template>
              <form @submit.prevent="saveMedecin" class="space-y-4">
                <AppInput v-model="formMedecin.nom" label="Nom" required />
                <AppInput v-model="formMedecin.prenom" label="Prénom" required />
                <AppInput v-model="formMedecin.email" label="Email" type="email" required />
                <AppInput v-if="!isEditing" v-model="formMedecin.password" label="Mot de passe" type="password" required />
                <AppInput v-model="formMedecin.specialite" label="Spécialité" required />
                <div class="flex flex-col gap-2 pt-4">
                  <AppButton type="submit" custom-class="w-full">{{ isEditing ? 'Mettre à jour' : 'Ajouter' }}</AppButton>
                  <AppButton v-if="isEditing" variant="secondary" @click="resetForm" custom-class="w-full">Annuler</AppButton>
                </div>
              </form>
            </AppCard>
          </aside>

          <section class="lg:col-span-2">
            <div class="bg-white rounded-[2rem] border border-slate-200 overflow-hidden shadow-sm">
              <table class="w-full text-left">
                <thead>
                  <tr class="bg-slate-50/50 border-b border-slate-100">
                    <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Médecin</th>
                    <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Spécialité</th>
                    <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Actions</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                  <tr v-for="m in listData" :key="m.id" class="group hover:bg-slate-50/30 transition-all duration-300">
                    <td class="px-8 py-6">
                      <div class="flex items-center gap-4">
                        <div class="w-10 h-10 bg-slate-100 text-slate-600 flex items-center justify-center rounded-xl font-bold group-hover:bg-primary group-hover:text-white transition-all">
                          {{ m.user?.nom?.charAt(0) }}{{ m.user?.prenom?.charAt(0) }}
                        </div>
                        <div>
                          <p class="font-black text-slate-900 group-hover:text-primary transition-colors leading-none mb-1">{{ m.user?.nom }} {{ m.user?.prenom }}</p>
                          <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ m.user?.email }}</p>
                        </div>
                      </div>
                    </td>
                    <td class="px-8 py-6 text-sm font-bold text-slate-600 italic">
                      {{ m.specialite }}
                    </td>
                    <td class="px-8 py-6 text-right">
                      <div class="flex justify-end gap-2">
                        <button @click="isEditing = true; editId = m.id; formMedecin = { ...m.user, specialite: m.specialite }" class="p-2 hover:bg-primary/10 text-slate-400 hover:text-primary rounded-lg transition-all"><BaseIcon name="edit" :size="16" /></button>
                        <button @click="deleteItem('medecins', m.id)" class="p-2 hover:bg-red-50 text-slate-400 hover:text-red-500 rounded-lg transition-all"><BaseIcon name="trash" :size="16" /></button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </section>
        </div>
      </div>

      <!-- Content Infermiers -->
      <div v-if="currentTab === 'infermiers'" class="space-y-10 animate-in fade-in duration-500">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
          <aside class="lg:col-span-1 space-y-6">
            <AppCard>
              <template #header>
                <h3 class="text-lg font-black text-slate-900">{{ isEditing ? 'Modifier' : 'Ajouter' }} Infirmier</h3>
              </template>
              <form @submit.prevent="saveInfermier" class="space-y-4">
                <AppInput v-model="formInfermier.nom" label="Nom" required />
                <AppInput v-model="formInfermier.prenom" label="Prénom" required />
                <AppInput v-model="formInfermier.email" label="Email" type="email" required />
                <AppInput v-if="!isEditing" v-model="formInfermier.mot_de_passe" label="Mot de passe" type="password" required />
                <div class="flex flex-col gap-2 pt-4">
                  <AppButton type="submit" custom-class="w-full">{{ isEditing ? 'Mettre à jour' : 'Ajouter' }}</AppButton>
                  <AppButton v-if="isEditing" variant="secondary" @click="resetForm" custom-class="w-full">Annuler</AppButton>
                </div>
              </form>
            </AppCard>
          </aside>

          <section class="lg:col-span-2">
            <div class="bg-white rounded-[2rem] border border-slate-200 overflow-hidden shadow-sm">
              <table class="w-full text-left">
                <thead>
                  <tr class="bg-slate-50/50 border-b border-slate-100">
                    <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Infirmier(e)</th>
                    <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Contact</th>
                    <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Actions</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                  <tr v-for="inf in listData" :key="inf.id" class="group hover:bg-slate-50/30 transition-all duration-300">
                    <td class="px-8 py-6">
                      <div class="flex items-center gap-4">
                        <div class="w-10 h-10 bg-slate-100 text-slate-600 flex items-center justify-center rounded-xl font-bold group-hover:bg-primary group-hover:text-white transition-all">
                          {{ inf.user?.nom?.charAt(0) }}{{ inf.user?.prenom?.charAt(0) }}
                        </div>
                        <p class="font-black text-slate-900 group-hover:text-primary transition-colors">{{ inf.user?.nom }} {{ inf.user?.prenom }}</p>
                      </div>
                    </td>
                    <td class="px-8 py-6 text-sm font-bold text-slate-400">
                      {{ inf.user?.email }}
                    </td>
                    <td class="px-8 py-6 text-right">
                      <div class="flex justify-end gap-2">
                        <button @click="isEditing = true; editId = inf.id; formInfermier = { ...inf.user, mot_de_passe: '' }" class="p-2 hover:bg-primary/10 text-slate-400 hover:text-primary rounded-lg transition-all"><BaseIcon name="edit" :size="16" /></button>
                        <button @click="deleteItem('infermiers', inf.id)" class="p-2 hover:bg-red-50 text-slate-400 hover:text-red-500 rounded-lg transition-all"><BaseIcon name="trash" :size="16" /></button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </section>
        </div>
      </div>

      <!-- Content Toxicites -->
      <div v-if="currentTab === 'toxicites'" class="space-y-10 animate-in fade-in duration-500">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
          <aside class="lg:col-span-1 space-y-6">
            <AppCard>
              <template #header>
                <h3 class="text-lg font-black text-slate-900">{{ isEditing ? 'Modifier' : 'Ajouter' }} Toxicité</h3>
              </template>
              <form @submit.prevent="saveToxicite" class="space-y-6">
                <AppInput v-model="formToxicite.nom" label="Nom (ex: Anémie)" required />
                <div class="flex flex-col gap-1.5 w-full">
                  <label class="text-sm font-semibold text-slate-700">Description</label>
                  <textarea v-model="formToxicite.description" placeholder="Description courte..." class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-primary/20 outline-none transition-all resize-none h-24 text-sm font-medium"></textarea>
                </div>

                <div class="space-y-3">
                  <label class="text-xs font-black text-slate-400 uppercase tracking-widest block">Symptômes associés</label>
                  <div class="flex flex-wrap gap-2 p-3 bg-slate-50 rounded-2xl border border-slate-100 min-h-[60px]">
                    <span v-for="(s, index) in formToxicite.symptomes" :key="index" class="inline-flex items-center gap-2 px-3 py-1 bg-white border border-slate-200 rounded-full text-[10px] font-black uppercase tracking-tight text-slate-600 shadow-sm">
                      {{ s.nom }}
                      <button type="button" @click="s.id ? deleteSymptome(s.id) : removeSymptomeFromForm(index)" class="text-red-500 font-black hover:scale-125 transition-transform">×</button>
                    </span>
                    <button type="button" @click="showSymptomeForm = true" class="px-3 py-1 bg-primary/10 text-primary border border-primary/20 rounded-full text-[10px] font-black uppercase tracking-widest hover:bg-primary hover:text-white transition-all">+ Ajouter</button>
                  </div>
                </div>

                <div class="flex flex-col gap-2 pt-4">
                  <AppButton type="submit" custom-class="w-full">{{ isEditing ? 'Mettre à jour' : 'Enregistrer' }}</AppButton>
                  <AppButton v-if="isEditing" variant="secondary" @click="resetForm" custom-class="w-full">Annuler</AppButton>
                </div>
              </form>
            </AppCard>
          </aside>

          <section class="lg:col-span-2">
            <div class="bg-white rounded-[2rem] border border-slate-200 overflow-hidden shadow-sm">
              <table class="w-full text-left">
                <thead>
                  <tr class="bg-slate-50/50 border-b border-slate-100">
                    <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Toxicité</th>
                    <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Symptômes</th>
                    <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Actions</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                  <tr v-for="t in listData" :key="t.id" class="group hover:bg-slate-50/30 transition-all duration-300">
                    <td class="px-8 py-6">
                      <p class="font-black text-slate-900 group-hover:text-primary transition-colors mb-1">{{ t.nom }}</p>
                      <p class="text-xs text-slate-400 font-medium italic line-clamp-1">{{ t.description }}</p>
                    </td>
                    <td class="px-8 py-6">
                      <div class="flex flex-wrap gap-1">
                        <span v-for="s in t.symptomes" :key="s.id" class="px-2 py-0.5 bg-slate-100 text-slate-500 text-[9px] font-black uppercase tracking-widest rounded-md">
                          {{ s.nom }}
                        </span>
                        <span v-if="!t.symptomes?.length" class="text-[9px] font-bold text-slate-300 italic">Aucun</span>
                      </div>
                    </td>
                    <td class="px-8 py-6 text-right">
                      <div class="flex justify-end gap-2">
                        <button @click="preparerEditToxicite(t)" class="p-2 hover:bg-primary/10 text-slate-400 hover:text-primary rounded-lg transition-all"><BaseIcon name="edit" :size="16" /></button>
                        <button @click="deleteToxicite(t.id)" class="p-2 hover:bg-red-50 text-slate-400 hover:text-red-500 rounded-lg transition-all"><BaseIcon name="trash" :size="16" /></button>
                      </div>
                    </td>
                  </tr>
                  <tr v-if="!listData.length">
                    <td colspan="3" class="px-8 py-20 text-center">
                      <p class="text-slate-400 font-bold">Aucune toxicité trouvée.</p>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </section>
        </div>
      </div>
    </main>

    <!-- Symptome Modal -->
    <div v-if="showSymptomeForm" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm animate-in fade-in duration-300">
      <div class="w-full max-w-md bg-white rounded-3xl shadow-2xl overflow-hidden animate-in zoom-in-95 duration-300">
        <div class="p-8 border-b border-slate-100 flex justify-between items-center">
          <h4 class="text-xl font-black text-slate-900">Nouveau symptôme</h4>
          <button @click="showSymptomeForm = false" class="text-slate-400 text-2xl">×</button>
        </div>
        <div class="p-8 space-y-4">
          <AppInput v-model="tempSymptome.nom" label="Nom" placeholder="Ex: Fatigue intense" />
          <div class="flex flex-col gap-1.5 w-full">
            <label class="text-sm font-semibold text-slate-700">Description</label>
            <textarea v-model="tempSymptome.description" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white outline-none h-24 resize-none text-sm"></textarea>
          </div>
        </div>
        <div class="p-8 bg-slate-50 flex gap-4">
          <AppButton @click="addSymptomeToForm" custom-class="flex-1">Ajouter</AppButton>
          <AppButton variant="secondary" @click="showSymptomeForm = false" custom-class="flex-1">Fermer</AppButton>
        </div>
      </div>
    </div>
  </div>
</template>
