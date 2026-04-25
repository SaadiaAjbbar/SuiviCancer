<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import AppCard from '@/components/ui/AppCard.vue';
import AppButton from '@/components/ui/AppButton.vue';
import AppInput from '@/components/ui/AppInput.vue';
import BaseIcon from '@/components/ui/BaseIcon.vue';

const router = useRouter();
const token = localStorage.getItem('user_token');

const currentTab = ref('patients');
const listPatients = ref([]);
const medecinsDisponibles = ref([]);
const message = ref('');
const errorMessage = ref('');
const isLoading = ref(false);
const isEditing = ref(false);
const editingId = ref(null);

const formPatient = ref({
  nom: '',
  prenom: '',
  email: '',
  password: '',
  date_naissance: '',
  sexe: 'M',
  telephone: '',
  adresse: '',
  medecin_id: ''
});

const fetchPatients = async () => {
  try {
    const response = await fetch('http://localhost:8080/api/infirmiers/patients', {
      headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
    });
    const data = await response.json();
    listPatients.value = Array.isArray(data) ? data : (data.liste || []);
  } catch (e) { console.error(e); }
};

const fetchMedecinsMemeHopital = async () => {
  try {
    const response = await fetch('http://localhost:8080/api/infirmiers/medecins', {
      headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
    });
    const data = await response.json();
    medecinsDisponibles.value = Array.isArray(data) ? data : (data.liste || []);
  } catch (e) { console.error(e); }
};

const handleLogout = async () => {
  try {
    await fetch('http://localhost:8080/api/logout', {
      method: 'POST',
      headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
    });
  } catch (e) { console.error(e); } finally {
    localStorage.clear();
    router.push('/login');
  }
};

const enregistrerPatient = async () => {
  errorMessage.value = '';

  if (!formPatient.value.medecin_id) {
    alert("Veuillez sélectionner un médecin référent.");
    return;
  }

  isLoading.value = true;
  const url = isEditing.value
    ? `http://localhost:8080/api/infirmiers/patients/${editingId.value}`
    : 'http://localhost:8080/api/infirmiers/patients';

  const method = isEditing.value ? 'PUT' : 'POST';

  try {
    const payload = {
      ...formPatient.value,
      medecin_id: formPatient.value.medecin_id ? Number(formPatient.value.medecin_id) : null
    };

    const response = await fetch(url, {
      method,
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      },
      body: JSON.stringify(payload)
    });

    const responseData = await response.json().catch(() => ({}));

    if (response.ok) {
      message.value = isEditing.value ? "✅ Patient mis à jour !" : "✅ Admission réussie !";
      resetFormulaire();
      fetchPatients();
      setTimeout(() => message.value = '', 3000);
      return;
    }

    if (response.status === 422) {
      if (responseData?.errors) {
        const firstError = Object.values(responseData.errors)
          .flat()
          .find(Boolean);
        errorMessage.value = firstError || "Données invalides. Vérifiez les champs saisis.";
      } else {
        errorMessage.value = responseData?.message || "Données invalides. Vérifiez les champs saisis.";
      }
      return;
    }

    errorMessage.value = responseData?.message || "Une erreur est survenue lors de l'enregistrement du patient.";
  } catch (e) { console.error(e); } finally {
    isLoading.value = false;
  }
};

const preparerModification = (patient) => {
  isEditing.value = true;
  editingId.value = patient.id;
  formPatient.value = {
    nom: patient.user.nom,
    prenom: patient.user.prenom,
    email: patient.user.email,
    password: '',
    date_naissance: patient.date_naissance,
    sexe: patient.sexe,
    telephone: patient.telephone,
    adresse: patient.adresse || '',
    medecin_id: patient.medecin_id
  };
  window.scrollTo({ top: 0, behavior: 'smooth' });
};

const supprimerPatient = async (id) => {
  if (!confirm("Voulez-vous vraiment supprimer ce patient ?")) return;
  try {
    const response = await fetch(`http://localhost:8080/api/infirmiers/patients/${id}`, {
      method: 'DELETE',
      headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
    });
    if (response.ok) {
      message.value = "🗑️ Patient supprimé";
      fetchPatients();
      setTimeout(() => message.value = '', 3000);
    }
  } catch (e) { console.error(e); }
};

const resetFormulaire = () => {
  isEditing.value = false;
  editingId.value = null;
  errorMessage.value = '';
  formPatient.value = {
    nom: '', prenom: '', email: '', password: '',
    date_naissance: '', sexe: 'M', telephone: '', adresse: '', medecin_id: ''
  };
};

onMounted(() => {
  fetchPatients();
  fetchMedecinsMemeHopital();
});
</script>

<template>
  <div class="min-h-screen bg-slate-50 font-sans">
    <!-- Premium Nurse Navbar -->
    <nav class="bg-white border-b border-slate-200 sticky top-0 z-50 shadow-sm">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-20">
          <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-primary/10 rounded-2xl flex items-center justify-center shadow-lg shadow-primary/5 border border-primary/10 text-primary">
              <BaseIcon name="hospital" :size="24" stroke-width="2.5" />
            </div>
            <div>
              <span class="text-xl font-black text-slate-900 tracking-tight block leading-none">E-Sante <span class="text-primary">Infirmier</span></span>
              <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1 block">Gestion des Admissions</span>
            </div>
          </div>

          <div class="flex items-center gap-6">
            <div class="hidden md:flex items-center gap-2 px-4 py-2 bg-emerald-50 text-emerald-600 rounded-full text-[10px] font-black uppercase tracking-widest border border-emerald-100">
              <span class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></span>
              Service Actif
            </div>
            <AppButton variant="secondary" size="sm" @click="handleLogout" custom-class="group">
              <BaseIcon name="logout" :size="14" class="mr-2 group-hover:-translate-x-1 transition-transform" />
              Déconnexion
            </AppButton>
          </div>
        </div>
      </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
      <div v-if="message" class="fixed top-24 right-8 z-50 p-4 bg-primary text-white rounded-2xl shadow-xl animate-in slide-in-from-right-10 font-bold">
        {{ message }}
      </div>

      <div v-if="errorMessage" class="fixed top-24 left-8 z-50 p-4 bg-red-600 text-white rounded-2xl shadow-xl font-bold max-w-md">
        {{ errorMessage }}
      </div>

      <div class="space-y-10">
        <!-- Admission Form Section -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
          <div class="lg:col-span-1 space-y-6">
            <h2 class="text-3xl font-black text-slate-900 tracking-tight leading-tight">
              {{ isEditing ? 'Modifier le Dossier' : 'Admission Patient' }}
            </h2>
            <p class="text-slate-500 font-medium italic">
              {{ isEditing ? 'Mettez à jour les informations du dossier patient sélectionné.' : 'Enregistrez un nouveau patient et assignez-le à un médecin référent.' }}
            </p>

            <div class="p-6 bg-primary/5 rounded-3xl border border-primary/10">
              <h4 class="text-xs font-black text-primary uppercase tracking-widest mb-2">Conseil</h4>
              <p class="text-sm text-slate-600 leading-relaxed font-medium">Assurez-vous que l'adresse email est correcte pour que le patient puisse accéder à son espace.</p>
            </div>
          </div>

          <div class="lg:col-span-2">
            <AppCard>
              <form @submit.prevent="enregistrerPatient" class="space-y-8">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                  <AppInput v-model="formPatient.nom" label="Nom" placeholder="Ex: Dupont" required />
                  <AppInput v-model="formPatient.prenom" label="Prénom" placeholder="Ex: Jean" required />
                  <AppInput v-model="formPatient.email" label="Email" type="email" placeholder="jean.dupont@email.com" required />
                  <AppInput v-model="formPatient.password" label="Mot de passe" type="password" :placeholder="isEditing ? 'Laisser vide pour ne pas changer' : '********'" :required="!isEditing" />
                  <AppInput v-model="formPatient.date_naissance" label="Date de naissance" type="date" required />

                  <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Sexe</label>
                    <div class="flex gap-4">
                      <label class="flex-1 flex items-center justify-center gap-2 p-3 bg-slate-50 border border-slate-200 rounded-xl cursor-pointer hover:bg-white transition-all has-[:checked]:bg-primary/5 has-[:checked]:border-primary has-[:checked]:text-primary font-bold text-sm">
                        <input type="radio" v-model="formPatient.sexe" value="M" class="hidden" />
                        Masculin
                      </label>
                      <label class="flex-1 flex items-center justify-center gap-2 p-3 bg-slate-50 border border-slate-200 rounded-xl cursor-pointer hover:bg-white transition-all has-[:checked]:bg-primary/5 has-[:checked]:border-primary has-[:checked]:text-primary font-bold text-sm">
                        <input type="radio" v-model="formPatient.sexe" value="F" class="hidden" />
                        Féminin
                      </label>
                    </div>
                  </div>

                  <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Médecin Référent</label>
                    <select v-model="formPatient.medecin_id" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-primary/20 outline-none transition-all font-medium text-slate-700">
                      <option value="" disabled>Choisir un médecin</option>
                      <option v-for="m in medecinsDisponibles" :key="m.id" :value="m.id">
                        Dr. {{ m.user?.nom }} ({{ m.specialite }})
                      </option>
                    </select>
                  </div>

                  <AppInput v-model="formPatient.telephone" label="Téléphone" placeholder="06XXXXXXXX" required />
                  <div class="sm:col-span-2">
                    <AppInput v-model="formPatient.adresse" label="Adresse" placeholder="Adresse complète" />
                  </div>
                </div>

                <div class="flex flex-col sm:flex-row gap-4 pt-4">
                  <AppButton type="submit" :loading="isLoading" custom-class="flex-1 py-4 text-lg">
                    {{ isEditing ? 'Enregistrer les modifications' : 'Valider l\'admission' }}
                  </AppButton>
                  <AppButton v-if="isEditing" variant="secondary" @click="resetFormulaire" custom-class="flex-1 py-4 text-lg">
                    Annuler
                  </AppButton>
                </div>
              </form>
            </AppCard>
          </div>
        </div>

        <!-- Patients List Section -->
        <div class="space-y-6">
          <div class="flex items-center justify-between px-2">
            <h3 class="text-xl font-black text-slate-900 tracking-tight flex items-center gap-3">
              <BaseIcon name="users" :size="24" class="text-slate-300" /> Liste des patients hospitalisés
            </h3>
            <span class="text-xs font-black text-slate-400 uppercase tracking-widest bg-white px-3 py-1 rounded-full border border-slate-100">
              {{ listPatients.length }} Patients
            </span>
          </div>

          <div class="bg-white rounded-[2rem] border border-slate-200 overflow-hidden shadow-sm">
            <table class="w-full text-left">
              <thead>
                <tr class="bg-slate-50/50 border-b border-slate-100">
                  <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Patient</th>
                  <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Médecin Référent</th>
                  <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Détails</th>
                  <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-50">
                <tr v-for="p in listPatients" :key="p.id" class="group hover:bg-slate-50/30 transition-all duration-300">
                  <td class="px-8 py-6">
                    <div class="flex items-center gap-4">
                      <div class="w-12 h-12 bg-slate-100 text-slate-600 flex items-center justify-center rounded-2xl font-bold border border-slate-200 group-hover:bg-primary group-hover:text-white transition-all duration-500">
                        {{ p.user?.nom?.charAt(0) }}{{ p.user?.prenom?.charAt(0) }}
                      </div>
                      <div>
                        <p class="font-black text-slate-900 leading-none mb-1 group-hover:text-primary transition-colors">{{ p.user?.nom }} {{ p.user?.prenom }}</p>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ p.user?.email }}</p>
                      </div>
                    </div>
                  </td>
                  <td class="px-8 py-6">
                    <div class="flex items-center gap-2">
                      <span class="px-3 py-1 bg-blue-50 text-blue-600 text-[10px] font-black uppercase tracking-widest rounded-full border border-blue-100">
                        Dr. {{ p.medecin?.user?.nom || 'Non assigné' }}
                      </span>
                    </div>
                  </td>
                  <td class="px-8 py-6">
                    <div class="space-y-1">
                      <p class="text-xs font-bold text-slate-600 flex items-center gap-2">
                        <span class="text-[10px] grayscale group-hover:grayscale-0 transition-all">🚻</span> {{ p.sexe }}
                      </p>
                      <p class="text-xs font-bold text-slate-600 flex items-center gap-2">
                        <span class="text-[10px] grayscale group-hover:grayscale-0 transition-all">📞</span> {{ p.telephone }}
                      </p>
                    </div>
                  </td>
                  <td class="px-8 py-6 text-right">
                    <div class="flex justify-end gap-2">
                      <button @click="preparerModification(p)" class="p-3 bg-slate-50 hover:bg-primary/10 text-slate-400 hover:text-primary rounded-xl transition-all" title="Modifier">
                        <BaseIcon name="edit" :size="16" />
                      </button>
                      <button @click="supprimerPatient(p.id)" class="p-3 bg-slate-50 hover:bg-red-50 text-slate-400 hover:text-red-500 rounded-xl transition-all" title="Supprimer">
                        <BaseIcon name="trash" :size="16" />
                      </button>
                    </div>
                  </td>
                </tr>
                <tr v-if="listPatients.length === 0">
                  <td colspan="4" class="px-8 py-20 text-center">
                    <div class="text-6xl mb-4">🏥</div>
                    <h3 class="text-lg font-bold text-slate-900">Aucun patient actif</h3>
                    <p class="text-slate-400 font-medium">Commencez par enregistrer un patient via le formulaire ci-dessus.</p>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 py-10 mt-20">
      <div class="max-w-7xl mx-auto px-4 text-center">
        <p class="text-xs font-black text-slate-400 uppercase tracking-[0.2em]">&copy; 2026 E-Sante Platform — Espace Professionnel</p>
      </div>
    </footer>
  </div>
</template>
