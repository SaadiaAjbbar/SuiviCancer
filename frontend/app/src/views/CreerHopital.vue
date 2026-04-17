<script setup>
import { ref, onMounted } from 'vue';

// --- État de l'UI ---
const step = ref('list'); // 'list', 'create_hopital', 'create_admin', 'edit'
const isLoading = ref(false);
const messageSuccess = ref('');
const errorMessage = ref('');
const token = localStorage.getItem('user_token');

// --- Données ---
const hopitaux = ref([]);
const hopitalIdCree = ref(null); // Utilisé pour l'affectation de l'admin
const hopitalEnEdition = ref(null);

// --- Formulaires ---
const formHopital = ref({ nom: '', adresse: '', telephone: '', email: '' });
const formAdmin = ref({ nom: '', prenom: '', email: '', mot_de_passe: '' });

// 1. CHARGER LA LISTE (READ)
const fetchHopitaux = async () => {
  try {
    const response = await fetch('http://localhost:8080/api/hopitaux', {
      method: 'GET',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    });
    const result = await response.json();

    console.log("Data li jat men l-backend:", result); // T-akked chnou jay hna

    // Ila kan l-backend kiy-rejje3 { data: [...] }
    if (result.liste) {
      hopitaux.value = result.liste;
    } else {
      // Ila kan kiy-rejje3 [...] direct
      hopitaux.value = result;
    }
  } catch (e) {
    console.error("Erreur f l-fetch:", e);
  }
};

onMounted(fetchHopitaux);

// 2. CRÉER HÔPITAL (CREATE)
const creerHopital = async () => {
  isLoading.value = true;
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
    }
  } catch (e) { errorMessage.value = "Erreur lors de la création"; }
  finally { isLoading.value = false; }
};

// 3. CRÉER ADMIN (AFFECTATION)
const creerAdmin = async () => {
  isLoading.value = true;
  try {
    const response = await fetch('http://localhost:8080/api/admins-hopital', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' },
      body: JSON.stringify({ ...formAdmin.value, hopital_id: hopitalIdCree.value })
    });
    if (response.ok) {
      messageSuccess.value = "Hôpital et Admin créés avec succès !";
      resetForms();
    }
  } catch (e) { errorMessage.value = "Erreur création admin"; }
  finally { isLoading.value = false; }
};

// 4. SUPPRIMER (DELETE)
const supprimerHopital = async (id) => {
  if (!confirm("Voulez-vous vraiment supprimer cet hôpital ?")) return;
  try {
    let response = await fetch(`http://localhost:8080/api/hopitaux/${id}`, {
      method: 'DELETE',
      headers: { 'Authorization': `Bearer ${token}` }
    });
    console.log("Response delete:", response);
  } catch (e) { alert("Erreur suppression"); }
};

// 5. MODIFIER (UPDATE)
const preparerModification = (h) => {
  hopitalEnEdition.value = h.id;
  formHopital.value = { ...h };
  step.value = 'edit';
};

const modifierHopital = async () => {
  isLoading.value = true;
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
    }
  } catch (e) { errorMessage.value = "Erreur modification"; }
  finally { isLoading.value = false; }
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
  <div class="dashboard">
    <h1>Gestion des Hôpitaux</h1>

    <div v-if="messageSuccess" class="alert success">{{ messageSuccess }} <button
        @click="messageSuccess = ''">X</button>
    </div>

    <button v-if="step === 'list'" @click="step = 'create_hopital'" class="btn-add">+ Ajouter un Hôpital</button>

    <div v-if="step === 'create_hopital' || step === 'edit'" class="card">
      <h2>{{ step === 'edit' ? 'Modifier' : 'Créer' }} Hôpital</h2>
      <form @submit.prevent="step === 'edit' ? modifierHopital() : creerHopital()">
        <input v-model="formHopital.nom" placeholder="Nom" required>
        <input v-model="formHopital.adresse" placeholder="Adresse" required>
        <input v-model="formHopital.telephone" placeholder="Téléphone" required>
        <input v-model="formHopital.email" type="email" placeholder="Email" required>
        <button type="submit" :disabled="isLoading">{{ step === 'edit' ? 'Mettre à jour' : 'Suivant' }}</button>
        <button type="button" @click="resetForms" class="btn-back">Annuler</button>
      </form>
    </div>

    <div v-if="step === 'create_admin'" class="card">
      <h2>2. Créer l'Admin de l'Hôpital</h2>
      <form @submit.prevent="creerAdmin">
        <input v-model="formAdmin.nom" placeholder="Nom Admin" required>
        <input v-model="formAdmin.prenom" placeholder="Prénom Admin" required>
        <input v-model="formAdmin.email" type="email" placeholder="Email Admin" required>
        <input v-model="formAdmin.mot_de_passe" type="password" placeholder="Mot de passe" required>
        <button type="submit" :disabled="isLoading" class="btn-admin">Finaliser</button>
      </form>
    </div>

    <div v-if="step === 'list'" class="list-container">
      <table>
        <thead>
          <tr>
            <th>Nom</th>
            <th>Email</th>
            <th>Téléphone</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="h in hopitaux" :key="h.id">
            <td>{{ h.nom }}</td>
            <td>{{ h.email }}</td>
            <td>{{ h.telephone }}</td>
            <td>
              <button @click="preparerModification(h)" class="btn-edit">Modifier</button>
              <button @click="supprimerHopital(h.id)" class="btn-delete">Supprimer</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<style scoped>
.dashboard {
  padding: 30px;
  max-width: 900px;
  margin: auto;
}

.card {
  background: #f9f9f9;
  padding: 20px;
  border-radius: 8px;
  margin-bottom: 20px;
}

input {
  width: 100%;
  padding: 8px;
  margin-bottom: 10px;
  border-radius: 4px;
  border: 1px solid #ccc;
}

table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 20px;
}

th,
td {
  border: 1px solid #ddd;
  padding: 12px;
  text-align: left;
}

th {
  background-color: #f4f4f4;
}

.btn-add {
  background: #3498db;
  color: white;
  padding: 10px 20px;
  border: none;
  cursor: pointer;
  border-radius: 5px;
}

.btn-edit {
  background: #f1c40f;
  color: white;
  margin-right: 5px;
  border: none;
  padding: 5px 10px;
  cursor: pointer;
}

.btn-delete {
  background: #e74c3c;
  color: white;
  border: none;
  padding: 5px 10px;
  cursor: pointer;
}

.success {
  background: #d4edda;
  color: #155724;
  padding: 15px;
  margin-bottom: 20px;
  border-radius: 5px;
}

button:disabled {
  opacity: 0.5;
}
</style>
