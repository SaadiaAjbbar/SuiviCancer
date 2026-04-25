<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import AppCard from '@/components/ui/AppCard.vue';
import AppButton from '@/components/ui/AppButton.vue';
import AppInput from '@/components/ui/AppInput.vue';

const step = ref('list'); // 'list', 'create_hopital', 'create_admin', 'edit'
const isLoading = ref(false);
const messageSuccess = ref('');
const errorMessage = ref('');
const token = localStorage.getItem('user_token');
const router = useRouter();

const hopitaux = ref([]);
const hopitalIdCree = ref(null);
const hopitalEnEdition = ref(null);

const formHopital = ref({ nom: '', adresse: '', telephone: '', email: '' });
const formAdmin = ref({ nom: '', prenom: '', email: '', mot_de_passe: '' });

const fetchHopitaux = async () => {
  try {
    const response = await fetch('http://localhost:8080/api/hopitaux', {
      headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
    });
    const result = await response.json();
    hopitaux.value = result.liste || result;
  } catch (e) {
    console.error(e);
  }
};

onMounted(fetchHopitaux);

const creerHopital = async () => {
  isLoading.value = true;
  errorMessage.value = '';
  try {
    const response = await fetch('http://localhost:8080/api/hopitaux', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' },
      body: JSON.stringify(formHopital.value)
    });
    const result = await response.json();
    if (response.ok) {
      hopitalIdCree.value = result.data.id;
      step.value = 'create_admin';
      fetchHopitaux();
    } else {
      errorMessage.value = result.message || "Erreur lors de la création de l'hôpital";
    }
  } catch (e) { 
    errorMessage.value = "Erreur réseau lors de la création"; 
    console.error(e);
  } finally { 
    isLoading.value = false; 
  }
};

const creerAdmin = async () => {
  isLoading.value = true;
  errorMessage.value = '';
  try {
    const response = await fetch('http://localhost:8080/api/admins-hopital', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' },
      body: JSON.stringify({ ...formAdmin.value, hopital_id: hopitalIdCree.value })
    });
    if (response.ok) {
      messageSuccess.value = "Hôpital et Admin créés avec succès !";
      resetForms();
      setTimeout(() => messageSuccess.value = '', 3000);
    } else {
      const result = await response.json();
      errorMessage.value = result.message || "Erreur lors de la création de l'administrateur";
    }
  } catch (e) { 
    errorMessage.value = "Erreur réseau lors de la création de l'admin"; 
    console.error(e);
  } finally { 
    isLoading.value = false; 
  }
};

const supprimerHopital = async (id) => {
  if (!confirm("Voulez-vous vraiment supprimer cet hôpital ?")) return;
  try {
    await fetch(`http://localhost:8080/api/hopitaux/${id}`, {
      method: 'DELETE',
      headers: { 'Authorization': `Bearer ${token}` }
    });
    fetchHopitaux();
  } catch (e) { console.error(e); }
};

const preparerModification = (h) => {
  hopitalEnEdition.value = h.id;
  formHopital.value = { ...h };
  step.value = 'edit';
};

const modifierHopital = async () => {
  isLoading.value = true;
  errorMessage.value = '';
  try {
    const response = await fetch(`http://localhost:8080/api/hopitaux/${hopitalEnEdition.value}`, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' },
      body: JSON.stringify(formHopital.value)
    });
    if (response.ok) {
      messageSuccess.value = "Hôpital mis à jour !";
      resetForms();
      fetchHopitaux();
      setTimeout(() => messageSuccess.value = '', 3000);
    } else {
      const result = await response.json();
      errorMessage.value = result.message || "Erreur lors de la modification de l'hôpital";
    }
  } catch (e) { 
    errorMessage.value = "Erreur réseau lors de la modification"; 
    console.error(e);
  } finally { 
    isLoading.value = false; 
  }
};

const logout = () => {
  localStorage.clear();
  router.push('/login');
};

const resetForms = () => {
  step.value = 'list';
  hopitalIdCree.value = null;
  hopitalEnEdition.value = null;
  formHopital.value = { nom: '', adresse: '', telephone: '', email: '' };
  formAdmin.value = { nom: '', prenom: '', email: '', mot_de_passe: '' };
};
</script>

<template>
  <div class="min-h-screen bg-slate-50 font-sans pb-20">
    <nav class="bg-white border-b border-slate-200 sticky top-0 z-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-20">
          <div class="flex items-center gap-4">
            <div class="w-10 h-10 bg-slate-900 rounded-xl flex items-center justify-center">
              <span class="text-white font-black text-xl italic">S</span>
            </div>
            <span class="text-xl font-black text-slate-900 tracking-tight">Super <span class="text-primary">Admin</span></span>
          </div>
          <div class="flex items-center gap-4">
            <AppButton variant="secondary" size="sm" @click="logout">Déconnexion</AppButton>
          </div>
        </div>
      </div>
    </nav>

    <main class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
      <div v-if="messageSuccess" class="fixed top-24 right-8 z-50 p-4 bg-emerald-500 text-white rounded-2xl shadow-xl animate-in slide-in-from-right-10 font-bold flex items-center gap-2">
        <span>✅</span> {{ messageSuccess }}
      </div>

      <div v-if="errorMessage" class="fixed top-24 right-8 z-50 p-4 bg-red-500 text-white rounded-2xl shadow-xl animate-in slide-in-from-right-10 font-bold flex items-center gap-2">
        <span>⚠️</span> {{ errorMessage }}
        <button @click="errorMessage = ''" class="ml-2 hover:opacity-70">×</button>
      </div>

      <div class="flex flex-col gap-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-black text-slate-900 tracking-tight">Gestion des Hôpitaux</h1>
            <p class="text-slate-500 font-medium">Inscrivez de nouveaux établissements et gérez leurs accès administrateurs.</p>
          </div>
          <AppButton v-if="step === 'list'" @click="step = 'create_hopital'">+ Ajouter un Hôpital</AppButton>
        </div>

        <!-- List View -->
        <div v-if="step === 'list'" class="bg-white rounded-[2rem] border border-slate-200 overflow-hidden shadow-sm animate-in fade-in duration-500">
          <table class="w-full text-left">
            <thead>
              <tr class="bg-slate-50/50 border-b border-slate-100">
                <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Établissement</th>
                <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Contact</th>
                <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
              <tr v-for="h in hopitaux" :key="h.id" class="group hover:bg-slate-50/30 transition-all duration-300">
                <td class="px-8 py-6">
                  <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-slate-100 text-slate-600 flex items-center justify-center rounded-2xl font-bold border border-slate-200 group-hover:bg-primary group-hover:text-white transition-all duration-500">
                      {{ h.nom?.charAt(0) }}
                    </div>
                    <div>
                      <p class="font-black text-slate-900 leading-none mb-1 group-hover:text-primary transition-colors uppercase tracking-tight">{{ h.nom }}</p>
                      <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest italic">{{ h.adresse }}</p>
                    </div>
                  </div>
                </td>
                <td class="px-8 py-6">
                  <div class="space-y-1">
                    <p class="text-xs font-bold text-slate-600 flex items-center gap-2">
                      <span class="text-slate-300 text-base">📧</span> {{ h.email }}
                    </p>
                    <p class="text-xs font-bold text-slate-600 flex items-center gap-2">
                      <span class="text-slate-300 text-base">📞</span> {{ h.telephone }}
                    </p>
                  </div>
                </td>
                <td class="px-8 py-6 text-right">
                  <div class="flex justify-end gap-2">
                    <button @click="preparerModification(h)" class="p-3 bg-slate-50 hover:bg-primary/10 text-slate-400 hover:text-primary rounded-xl transition-all" title="Modifier">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                      </svg>
                    </button>
                    <button @click="supprimerHopital(h.id)" class="p-3 bg-slate-50 hover:bg-red-50 text-slate-400 hover:text-red-500 rounded-xl transition-all" title="Supprimer">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Creation Steps -->
        <div v-if="step !== 'list'" class="max-w-2xl mx-auto w-full animate-in zoom-in-95 duration-300">
           <AppCard>
              <div v-if="step === 'create_hopital' || step === 'edit'" class="space-y-8">
                <div class="flex items-center gap-4 border-b border-slate-100 pb-6">
                  <div class="w-10 h-10 bg-primary/10 text-primary rounded-full flex items-center justify-center font-black">1</div>
                  <h2 class="text-xl font-black text-slate-900">{{ step === 'edit' ? 'Modifier' : 'Détails de l\'Hôpital' }}</h2>
                </div>
                <form @submit.prevent="step === 'edit' ? modifierHopital() : creerHopital()" class="space-y-6">
                  <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <AppInput v-model="formHopital.nom" label="Nom de l'établissement" placeholder="Hôpital Central" required />
                    <AppInput v-model="formHopital.email" label="Email de contact" type="email" placeholder="contact@hopital.com" required />
                    <AppInput v-model="formHopital.telephone" label="Téléphone" placeholder="05XXXXXXXX" required />
                    <AppInput v-model="formHopital.adresse" label="Adresse physique" placeholder="Rue de la Paix" required />
                  </div>
                  <div class="flex gap-4 pt-4 border-t border-slate-50">
                    <AppButton type="submit" :loading="isLoading" custom-class="flex-1">{{ step === 'edit' ? 'Mettre à jour' : 'Suivant' }}</AppButton>
                    <AppButton variant="secondary" @click="resetForms" custom-class="flex-1">Annuler</AppButton>
                  </div>
                </form>
              </div>

              <div v-if="step === 'create_admin'" class="space-y-8">
                <div class="flex items-center gap-4 border-b border-slate-100 pb-6">
                  <div class="w-10 h-10 bg-primary/10 text-primary rounded-full flex items-center justify-center font-black">2</div>
                  <h2 class="text-xl font-black text-slate-900">Créer l'Admin de l'Hôpital</h2>
                </div>
                <form @submit.prevent="creerAdmin" class="space-y-6">
                   <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <AppInput v-model="formAdmin.nom" label="Nom de l'Admin" required />
                    <AppInput v-model="formAdmin.prenom" label="Prénom de l'Admin" required />
                    <AppInput v-model="formAdmin.email" label="Email Admin" type="email" required />
                    <AppInput v-model="formAdmin.mot_de_passe" label="Mot de passe" type="password" required />
                  </div>
                  <div class="pt-4 border-t border-slate-50">
                    <AppButton type="submit" :loading="isLoading" custom-class="w-full">Finaliser l'inscription</AppButton>
                  </div>
                </form>
              </div>
           </AppCard>
        </div>
      </div>
    </main>
  </div>
</template>
