<script setup>
import { ref, onMounted } from 'vue';

const token = localStorage.getItem('user_token');
const currentTab = ref('patients'); // patients, rendez-vous, notifications
const listPatients = ref([]);
const message = ref('');

// Formulaire Patient
const formPatient = ref({
  nom: '',
  prenom: '',
  date_naissance: '',
  sexe: 'M',
  telephone: '',
  adresse: ''
});

// --- CHARGEMENT ---
const fetchPatients = async () => {
  try {
    const response = await fetch('http://localhost:8080/api/patients', {
      headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
    });
    listPatients.value = await response.json();
  } catch (e) { console.error("Erreur patients"); }
};

// --- ACTIONS ---
const ajouterPatient = async () => {
  try {
    const response = await fetch('http://localhost:8080/api/patients', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      },
      body: JSON.stringify(formPatient.value)
    });
    if (response.ok) {
      message.value = "Patient ajouté avec succès !";
      formPatient.value = { nom: '', prenom: '', date_naissance: '', sexe: 'M', telephone: '', adresse: '' };
      fetchPatients();
    }
  } catch (e) { console.error(e); }
};

onMounted(fetchPatients);
</script>

<template>
  <div class="infirmier-container">
    <nav class="navbar">
      <div class="logo">E-Sante | Infirmier</div>
      <ul class="nav-links">
        <li @click="currentTab = 'patients'" :class="{ active: currentTab === 'patients' }">Patients</li>
        <li @click="currentTab = 'rendez-vous'" :class="{ active: currentTab === 'rendez-vous' }">Rendez-vous</li>
        <li @click="currentTab = 'notifications'" :class="{ active: currentTab === 'notifications' }">Notifications</li>
      </ul>
      <button class="btn-logout">Déconnexion</button>
    </nav>

    <div class="content">
      <p v-if="message" class="alert">{{ message }}</p>

      <div v-if="currentTab === 'patients'">
        <div class="header-section">
          <h2>Gestion des Patients</h2>
          <button @click="currentTab = 'patients'" class="btn-primary">+ Nouveau Patient</button>
        </div>

        <section class="card form-box">
          <h3>Formulaire d'admission</h3>
          <form @submit.prevent="ajouterPatient" class="grid-form">
            <input v-model="formPatient.nom" placeholder="Nom" required>
            <input v-model="formPatient.prenom" placeholder="Prénom" required>
            <input v-model="formPatient.date_naissance" type="date" required>
            <select v-model="formPatient.sexe">
              <option value="M">Masculin</option>
              <option value="F">Féminin</option>
            </select>
            <input v-model="formPatient.telephone" placeholder="Téléphone">
            <input v-model="formPatient.adresse" placeholder="Adresse">
            <button type="submit" class="btn-submit">Enregistrer le patient</button>
          </form>
        </section>

        <table class="table-custom">
          <thead>
            <tr>
              <th>Nom</th>
              <th>Prénom</th>
              <th>Sexe</th>
              <th>Téléphone</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="p in listPatients" :key="p.id">
              <td>{{ p.nom }}</td>
              <td>{{ p.prenom }}</td>
              <td>{{ p.sexe }}</td>
              <td>{{ p.telephone }}</td>
              <td>
                <button class="btn-view">Dossier</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-if="currentTab === 'rendez-vous'">
        <h2>Prise de Rendez-vous</h2>
        <p>Interface pour lier un patient à un médecin...</p>
      </div>

      <div v-if="currentTab === 'notifications'">
        <h2>Envoyer une Notification</h2>
        <p>Envoyer des rappels par Email/SMS aux patients...</p>
      </div>
    </div>
  </div>
</template>

<style scoped>
.navbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #2c3e50;
  color: white;
  padding: 15px 30px;
}

.nav-links {
  display: flex;
  list-style: none;
  gap: 20px;
}

.nav-links li {
  cursor: pointer;
  opacity: 0.8;
  transition: 0.3s;
}

.nav-links li.active,
.nav-links li:hover {
  opacity: 1;
  border-bottom: 2px solid #3498db;
}

.content {
  padding: 30px;
  max-width: 1100px;
  margin: auto;
}

.card {
  background: #fff;
  padding: 20px;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.grid-form {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 15px;
}

.grid-form input,
.grid-form select {
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 5px;
}

.btn-submit {
  background: #27ae60;
  color: white;
  border: none;
  grid-column: span 3;
  padding: 12px;
  cursor: pointer;
  border-radius: 5px;
  font-weight: bold;
}

.table-custom {
  width: 100%;
  border-collapse: collapse;
  margin-top: 30px;
}

.table-custom th,
.table-custom td {
  padding: 12px;
  text-align: left;
  border-bottom: 1px solid #eee;
}

.btn-primary {
  background: #3498db;
  color: white;
  border: none;
  padding: 10px 15px;
  border-radius: 5px;
  cursor: pointer;
}

.alert {
  background: #d4edda;
  color: #155724;
  padding: 15px;
  border-radius: 5px;
  margin-bottom: 20px;
}
</style>
