<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();
const token = localStorage.getItem('user_token');

// États
const currentTab = ref('patients');
const listPatients = ref([]);
const medecinsDisponibles = ref([]);
const message = ref('');
const isLoading = ref(false);
const isEditing = ref(false);
const editingId = ref(null);

// Formulaire
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

// --- CHARGEMENT DES DONNÉES ---

const fetchPatients = async () => {
  try {
    const response = await fetch('http://localhost:8080/api/infirmiers/patients', {
      headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
    });
    const data = await response.json();
    listPatients.value = Array.isArray(data) ? data : (data.liste || []);
  } catch (e) {
    console.error("Erreur patients", e);
  }
};

const fetchMedecinsMemeHopital = async () => {
  try {
    const response = await fetch('http://localhost:8080/api/infirmiers/medecins', {
      headers: { 'Authorization': `Bearer ${token}`,
       'Accept': 'application/json' }
    });
    const data = await response.json();
    medecinsDisponibles.value = Array.isArray(data) ? data : (data.liste || []);
  } catch (e) {
    console.error("Erreur médecins", e);
  }
};

// --- ACTIONS ---

const handleLogout = async () => {
  try {
    await fetch('http://localhost:8080/api/logout', {
      method: 'POST',
      headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
    });
  } catch (e) {
    console.error("Erreur déconnexion", e);
  } finally {
    localStorage.removeItem('user_token');
    localStorage.removeItem('user_role');
    router.push('/login');
  }
};

// Soumission du formulaire (Ajout ou Modification)
const enregistrerPatient = async () => {
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
    const response = await fetch(url, {
      method: method,
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      },
      body: JSON.stringify(formPatient.value)
    });

    if (response.ok) {
      message.value = isEditing.value ? "Patient mis à jour avec succès !" : "Patient enregistré avec succès !";
      resetFormulaire();
      fetchPatients();
      // Effacer le message après 3 secondes
      setTimeout(() => message.value = '', 3000);
    } else {
      const errorData = await response.json();
      alert("Erreur: " + (errorData.message || "Action impossible"));
    }
  } catch (e) {
    console.error(e);
  } finally {
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
    password: '', // On ne remplit pas le password par sécurité
    date_naissance: patient.date_naissance,
    sexe: patient.sexe,
    telephone: patient.telephone,
    adresse: patient.adresse || '',
    medecin_id: patient.medecin_id
  };

  window.scrollTo({ top: 0, behavior: 'smooth' });
};

const supprimerPatient = async (id) => {
  if (!confirm("Voulez-vous vraiment supprimer ce patient ? Cette action est irréversible.")) return;

  try {
    const response = await fetch(`http://localhost:8080/api/infirmiers/patients/${id}`, {
      method: 'DELETE',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    });

    if (response.ok) {
      message.value = "Patient supprimé.";
      fetchPatients();
      setTimeout(() => message.value = '', 3000);
    }
  } catch (e) {
    console.error("Erreur suppression", e);
  }
};

const resetFormulaire = () => {
  isEditing.value = false;
  editingId.value = null;
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
  <div class="infirmier-container">
    <nav class="navbar">
      <div class="logo">E-Sante | Espace Infirmier</div>
      <ul class="nav-links">
        <li @click="currentTab = 'patients'" :class="{ active: currentTab === 'patients' }">Gestion Patients</li>
      </ul>
      <button class="btn-logout" @click="handleLogout">Déconnexion</button>
    </nav>

    <div class="content">
      <transition name="fade">
        <div v-if="message" class="alert-success">{{ message }}</div>
      </transition>

      <div v-if="currentTab === 'patients'">
        <section class="card form-box">
          <div class="form-header">
            <h3>{{ isEditing ? '📝 Modifier le Patient' : '👤 Admission Nouveau Patient' }}</h3>
            <button v-if="isEditing" @click="resetFormulaire" class="btn-cancel-top">Annuler l'édition</button>
          </div>

          <form @submit.prevent="enregistrerPatient" class="grid-form">
            <div class="input-group">
              <label>Nom</label>
              <input v-model="formPatient.nom" placeholder="Nom" required>
            </div>
            <div class="input-group">
              <label>Prénom</label>
              <input v-model="formPatient.prenom" placeholder="Prénom" required>
            </div>
            <div class="input-group">
              <label>Email</label>
              <input v-model="formPatient.email" type="email" placeholder="Email" required>
            </div>
            <div class="input-group">
              <label>Mot de passe {{ isEditing ? '(Optionnel)' : '' }}</label>
              <input v-model="formPatient.password" type="password" :required="!isEditing">
            </div>
            <div class="input-group">
              <label>Date de naissance</label>
              <input v-model="formPatient.date_naissance" type="date" required>
            </div>
            <div class="input-group">
              <label>Sexe</label>
              <select v-model="formPatient.sexe">
                <option value="M">Masculin</option>
                <option value="F">Féminin</option>
              </select>
            </div>
            <div class="input-group">
              <label>Médecin Référent</label>
              <select v-model="formPatient.medecin_id" required>
                <option value="" disabled>Choisir un médecin</option>
                <option v-for="m in medecinsDisponibles" :key="m.id" :value="m.id">
                  Dr. {{ m.user?.nom }} ({{ m.specialite }})
                </option>
              </select>
            </div>
            <div class="input-group">
              <label>Téléphone</label>
              <input v-model="formPatient.telephone" placeholder="06XXXXXXXX">
            </div>
            <div class="input-group full-width">
              <label>Adresse</label>
              <input v-model="formPatient.adresse" placeholder="Adresse complète">
            </div>

            <button type="submit" :class="isEditing ? 'btn-update' : 'btn-submit'" :disabled="isLoading">
              {{ isLoading ? 'Opération en cours...' : (isEditing ? 'Enregistrer les modifications' : 'Valider l\'admission') }}
            </button>
          </form>
        </section>

        <div class="table-container card">
          <h3>Liste des patients hospitalisés</h3>
          <table class="table-custom">
            <thead>
              <tr>
                <th>Patient</th>
                <th>Médecin Référent</th>
                <th>Sexe</th>
                <th>Contact</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="p in listPatients" :key="p.id">
                <td>
                  <div class="patient-info">
                    <span class="p-name">{{ p.user?.nom }} {{ p.user?.prenom }}</span>
                    <span class="p-email">{{ p.user?.email }}</span>
                  </div>
                </td>
                <td>
                  <span class="badge-med">Dr. {{ p.medecin?.user?.nom || 'Non assigné' }}</span>
                </td>
                <td>{{ p.sexe }}</td>
                <td>{{ p.telephone }}</td>
                <td class="actions-cell">
                  <button class="btn-edit" @click="preparerModification(p)" title="Modifier">
                    Modifier
                  </button>
                  <button class="btn-delete" @click="supprimerPatient(p.id)" title="Supprimer">
                    Supprimer
                  </button>
                </td>
              </tr>
              <tr v-if="listPatients.length === 0">
                <td colspan="5" style="text-align: center; padding: 2rem; color: #7f8c8d;">
                  Aucun patient trouvé.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Configuration Générale */
.infirmier-container {
  font-family: 'Inter', 'Segoe UI', sans-serif;
  background: #f0f2f5;
  min-height: 100vh;
  color: #2c3e50;
}

/* Navbar */
.navbar {
  background: #1a252f;
  color: white;
  display: flex;
  justify-content: space-between;
  padding: 0.8rem 5%;
  align-items: center;
  position: sticky;
  top: 0;
  z-index: 100;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.nav-links {
  display: flex;
  list-style: none;
  gap: 2.5rem;
}

.nav-links li {
  cursor: pointer;
  font-weight: 500;
  transition: color 0.3s;
}

.nav-links li.active {
  color: #3498db;
  border-bottom: 2px solid #3498db;
}

/* Contenu */
.content {
  padding: 2rem 5%;
  max-width: 1400px;
  margin: 0 auto;
}

.card {
  background: white;
  padding: 1.5rem;
  border-radius: 10px;
  box-shadow: 0 4px 6px rgba(0,0,0,0.05);
  margin-bottom: 2rem;
}

/* Formulaire */
.form-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
  border-bottom: 2px solid #f0f2f5;
  padding-bottom: 0.5rem;
}

.grid-form {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.2rem;
}

.input-group {
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
}

.input-group label {
  font-size: 0.85rem;
  font-weight: 600;
  color: #7f8c8d;
}

.full-width { grid-column: 1 / -1; }

input, select {
  padding: 0.7rem;
  border: 1px solid #dcdde1;
  border-radius: 6px;
  font-size: 0.95rem;
}

input:focus { border-color: #3498db; outline: none; }

/* Boutons */
.btn-submit {
  grid-column: 1 / -1;
  background: #27ae60;
  color: white;
  border: none;
  padding: 0.9rem;
  border-radius: 6px;
  font-weight: bold;
  cursor: pointer;
  transition: background 0.3s;
}

.btn-update {
  grid-column: 1 / -1;
  background: #f39c12;
  color: white;
  border: none;
  padding: 0.9rem;
  border-radius: 6px;
  font-weight: bold;
  cursor: pointer;
}

.btn-cancel-top {
  background: #95a5a6;
  color: white;
  border: none;
  padding: 5px 12px;
  border-radius: 4px;
  cursor: pointer;
}

/* Tableau */
.table-container h3 { margin-bottom: 1.2rem; }

.table-custom {
  width: 100%;
  border-collapse: collapse;
}

.table-custom th {
  text-align: left;
  background: #f8f9fa;
  padding: 1rem;
  color: #7f8c8d;
  font-size: 0.9rem;
}

.table-custom td { padding: 1rem; border-bottom: 1px solid #f1f2f6; }

.patient-info { display: flex; flex-direction: column; }
.p-name { font-weight: 600; color: #2c3e50; }
.p-email { font-size: 0.8rem; color: #95a5a6; }

.badge-med {
  background: #e1f5fe;
  color: #0288d1;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 0.85rem;
}

/* Actions */
.actions-cell { display: flex; gap: 0.5rem; }

.btn-edit {
  background: #3498db;
  color: white;
  border: none;
  padding: 6px 12px;
  border-radius: 4px;
  cursor: pointer;
}

.btn-delete {
  background: #e74c3c;
  color: white;
  border: none;
  padding: 6px 12px;
  border-radius: 4px;
  cursor: pointer;
}

.btn-logout {
  background: #e74c3c;
  color: white;
  border: none;
  padding: 6px 15px;
  border-radius: 5px;
  cursor: pointer;
}

/* Alertes */
.alert-success {
  background: #27ae60;
  color: white;
  padding: 1rem;
  border-radius: 6px;
  margin-bottom: 1.5rem;
  text-align: center;
}

/* Transitions */
.fade-enter-active, .fade-leave-active { transition: opacity 0.5s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
