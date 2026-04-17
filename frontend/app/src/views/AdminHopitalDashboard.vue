<script setup>
import { ref, onMounted } from 'vue';

const token = localStorage.getItem('user_token');
const currentTab = ref('medecins');
const listData = ref([]);
const isLoading = ref(false);
const messageSuccess = ref('');

// Formulaires
const formMedecin = ref({ nom: '', prenom: '', email: '', password: '', specialite: '' });
const formInfermier = ref({ nom: '', prenom: '', email: '', mot_de_passe: '' }); // Note: mot_de_passe pour le backend
const formToxicite = ref({ nom: '', description: '' });

const isEditing = ref(false);
const editId = ref(null);

// --- FONCTIONS DE CHARGEMENT ---
const fetchData = async (endpoint) => {
  isLoading.value = true;
  messageSuccess.value = '';
  try {
    const response = await fetch(`http://localhost:8080/api/${endpoint}`, {
      headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
    });
    const result = await response.json();
    // Le backend retourne la liste directe pour les infirmiers/médecins
    listData.value = Array.isArray(result) ? result : (result.liste || []);
  } catch (e) {
    console.error("Erreur de chargement", e);
  } finally {
    isLoading.value = false;
  }
};

// --- MÉDECINS ---
const saveMedecin = async () => {
  const method = isEditing.value ? 'PUT' : 'POST';
  const url = isEditing.value ? `http://localhost:8080/api/medecins/${editId.value}` : 'http://localhost:8080/api/medecins';

  const response = await fetch(url, {
    method,
    headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' },
    body: JSON.stringify(formMedecin.value)
  });
  if (response.ok) {
    messageSuccess.value = "Médecin enregistré !";
    resetForm();
    fetchData('medecins');
  }
};

// --- INFIRMIERS (NOUVEAU) ---
const saveInfermier = async () => {
  const method = isEditing.value ? 'PUT' : 'POST';
  const url = isEditing.value ? `http://localhost:8080/api/infermiers/${editId.value}` : 'http://localhost:8080/api/infermiers';

  const response = await fetch(url, {
    method,
    headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' },
    body: JSON.stringify(formInfermier.value)
  });

  if (response.ok) {
    messageSuccess.value = "Infirmier(e) enregistré(e) !";
    resetForm();
    fetchData('infermiers');
  }
};

const preparerEditInfermier = (inf) => {
  isEditing.value = true;
  editId.value = inf.id;
  formInfermier.value = {
    nom: inf.user.nom,
    prenom: inf.user.prenom,
    email: inf.user.email,
    mot_de_passe: '' // On laisse vide si on ne change pas le mot de passe
  };
};






// --- Données Symptômes ---
const formSymptome = ref({ nom: '', description: '', toxicite_id: null });
const showSymptomeForm = ref(false); // Pour afficher/masquer le petit formulaire

// --- Fonctions Toxicités ---
const saveToxicite = async () => {
  const method = isEditing.value ? 'PUT' : 'POST';
  const url = isEditing.value ? `http://localhost:8080/api/toxicites/${editId.value}` : 'http://localhost:8080/api/toxicites';

  const response = await fetch(url, {
    method,
    headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' },
    body: JSON.stringify(formToxicite.value)
  });
  if (response.ok) {
    messageSuccess.value = isEditing.value ? "Toxicité modifiée !" : "Toxicité ajoutée !";
    resetForm();
    fetchData('toxicites');
  }
};

// --- Fonctions Symptômes ---
const preparerSymptome = (toxiciteId) => {
  formSymptome.value.toxicite_id = toxiciteId;
  showSymptomeForm.value = true;
};

const saveSymptome = async () => {
  try {
    const response = await fetch('http://localhost:8080/api/symptomes', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' },
      body: JSON.stringify(formSymptome.value)
    });
    if (response.ok) {
      messageSuccess.value = "Symptôme ajouté avec succès !";
      formSymptome.value = { nom: '', description: '', toxicite_id: null };
      showSymptomeForm.value = false;
      fetchData('toxicites'); // Recharger pour voir le nouveau symptôme
    }
  } catch (e) { console.error(e); }
};

const deleteSymptome = async (id) => {
  if (!confirm("Supprimer ce symptôme ?")) return;
  await fetch(`http://localhost:8080/api/symptomes/${id}`, {
    method: 'DELETE',
    headers: { 'Authorization': `Bearer ${token}` }
  });
  fetchData('toxicites');
};




const deleteItem = async (endpoint, id) => {
  if (!confirm("Supprimer cet élément ?")) return;
  await fetch(`http://localhost:8080/api/${endpoint}/${id}`, {
    method: 'DELETE',
    headers: { 'Authorization': `Bearer ${token}` }
  });
  fetchData(currentTab.value);
};

const resetForm = () => {
  isEditing.value = false;
  editId.value = null;
  formMedecin.value = { nom: '', prenom: '', email: '', password: '', specialite: '' };
  formInfermier.value = { nom: '', prenom: '', email: '', mot_de_passe: '' };
  formToxicite.value = { nom: '', description: '' };
};

onMounted(() => fetchData('medecins'));
</script>

<template>
  <div class="admin-dashboard">
    <header>
      <h1>Dashboard Admin Hôpital</h1>
      <nav class="tabs">
        <button @click="currentTab = 'medecins'; resetForm(); fetchData('medecins')"
          :class="{ active: currentTab === 'medecins' }">Médecins</button>
        <button @click="currentTab = 'infermiers'; resetForm(); fetchData('infermiers')"
          :class="{ active: currentTab === 'infermiers' }">Infirmiers</button>
        <button @click="currentTab = 'toxicites'; resetForm(); fetchData('toxicites')"
          :class="{ active: currentTab === 'toxicites' }">Toxicités</button>
      </nav>
    </header>

    <p v-if="messageSuccess" class="alert">{{ messageSuccess }}</p>

    <div v-if="currentTab === 'medecins'">
      <section class="card">
        <h3>{{ isEditing ? 'Modifier' : 'Ajouter' }} un Médecin</h3>
        <form @submit.prevent="saveMedecin" class="form-grid">
          <input v-model="formMedecin.nom" placeholder="Nom">
          <input v-model="formMedecin.prenom" placeholder="Prénom">
          <input v-model="formMedecin.email" placeholder="Email">
          <input v-if="!isEditing" v-model="formMedecin.password" type="password" placeholder="Mot de passe">
          <input v-model="formMedecin.specialite" placeholder="Spécialité">
          <button type="submit" class="btn-save">{{ isEditing ? 'Mettre à jour' : 'Ajouter' }}</button>
          <button v-if="isEditing" type="button" @click="resetForm">Annuler</button>
        </form>
      </section>

      <table>
        <thead>
          <tr>
            <th>Nom Complet</th>
            <th>Spécialité</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="m in listData" :key="m.id">
            <td>{{ m.user?.nom }} {{ m.user?.prenom }}</td>
            <td>{{ m.specialite }}</td>
            <td>
              <button
                @click="isEditing = true; editId = m.id; formMedecin = { ...m.user, specialite: m.specialite }">Editer</button>
              <button @click="deleteItem('medecins', m.id)" class="btn-del">X</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="currentTab === 'infermiers'">
      <section class="card">
        <h3>{{ isEditing ? 'Modifier' : 'Ajouter' }} un(e) Infirmier(e)</h3>
        <form @submit.prevent="saveInfermier" class="form-grid">
          <input v-model="formInfermier.nom" placeholder="Nom">
          <input v-model="formInfermier.prenom" placeholder="Prénom">
          <input v-model="formInfermier.email" placeholder="Email">
          <input v-if="!isEditing" v-model="formInfermier.mot_de_passe" type="password" placeholder="Mot de passe">
          <button type="submit" class="btn-save">{{ isEditing ? 'Mettre à jour' : 'Ajouter' }}</button>
          <button v-if="isEditing" type="button" @click="resetForm">Annuler</button>
        </form>
      </section>

      <table>
        <thead>
          <tr>
            <th>Nom Complet</th>
            <th>Email</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="inf in listData" :key="inf.id">
            <td>{{ inf.user?.nom }} {{ inf.user?.prenom }}</td>
            <td>{{ inf.user?.email }}</td>
            <td>
              <button @click="preparerEditInfermier(inf)">Editer</button>
              <button @click="deleteItem('infermiers', inf.id)" class="btn-del">X</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="currentTab === 'toxicites'">
      <section class="card">
        <h3>{{ isEditing ? 'Modifier' : 'Ajouter' }} une Toxicité</h3>
        <form @submit.prevent="saveToxicite" class="form-grid">
          <input v-model="formToxicite.nom" placeholder="Nom de la toxicité (ex: Anémie)">
          <input v-model="formToxicite.description" placeholder="Description">
          <button type="submit" class="btn-save">{{ isEditing ? 'Mettre à jour' : 'Enregistrer' }}</button>
          <button v-if="isEditing" type="button" @click="resetForm">Annuler</button>
        </form>
      </section>

      <div v-if="showSymptomeForm" class="modal-overlay">
        <div class="card modal-content">
          <h4>Ajouter un symptôme</h4>
          <input v-model="formSymptome.nom" placeholder="Nom du symptôme (ex: Fatigue)">
          <textarea v-model="formSymptome.description" placeholder="Description"></textarea>
          <div class="actions">
            <button @click="saveSymptome" class="btn-save">Valider</button>
            <button @click="showSymptomeForm = false" class="btn-back">Fermer</button>
          </div>
        </div>
      </div>

      <div class="toxicite-list">
        <div v-for="t in listData" :key="t.id" class="toxicite-item">
          <div class="toxicite-header">
            <div>
              <strong>{{ t.nom }}</strong>
              <p class="desc">{{ t.description }}</p>
            </div>
            <div class="btns">
              <button
                @click="isEditing = true; editId = t.id; formToxicite = { nom: t.nom, description: t.description }"
                class="btn-edit-sml">Modifier</button>
              <button @click="deleteItem('toxicites', t.id)" class="btn-del-sml">Supprimer</button>
            </div>
          </div>

          <div class="symptomes-section">
            <h5>Symptômes associés :</h5>
            <ul>
              <li v-for="s in t.symptomes" :key="s.id">
                {{ s.nom }}
                <button @click="deleteSymptome(s.id)" class="btn-txt-del">supprimer</button>
              </li>
              <li v-if="!t.symptomes?.length" class="empty">Aucun symptôme enregistré</li>
            </ul>
            <button @click="preparerSymptome(t.id)" class="btn-add-symptome">+ Ajouter un symptôme</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Conserve tes styles précédents */
.admin-dashboard {
  padding: 20px;
  max-width: 1000px;
  margin: auto;
  font-family: sans-serif;
}

.tabs {
  margin-bottom: 20px;
  border-bottom: 2px solid #eee;
  padding-bottom: 10px;
  display: flex;
}

.tabs button {
  padding: 10px 20px;
  margin-right: 10px;
  border: none;
  cursor: pointer;
  background: #f4f4f4;
  border-radius: 5px;
  font-weight: bold;
}

.tabs button.active {
  background: #3498db;
  color: white;
}

.card {
  background: #f9f9f9;
  padding: 20px;
  border-radius: 8px;
  margin-bottom: 20px;
  border: 1px solid #ddd;
}

.form-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.form-grid input {
  padding: 10px;
  border: 1px solid #ccc;
  border-radius: 4px;
  flex: 1;
  min-width: 180px;
}

table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 20px;
  background: white;
}

th,
td {
  border: 1px solid #eee;
  padding: 12px;
  text-align: left;
}

th {
  background: #f4f4f4;
}

.btn-save {
  background: #27ae60;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 4px;
  cursor: pointer;
  font-weight: bold;
}

.btn-del {
  background: #e74c3c;
  color: white;
  border: none;
  padding: 5px 10px;
  border-radius: 4px;
  cursor: pointer;
}

.alert {
  background: #d4edda;
  color: #155724;
  padding: 15px;
  border-radius: 5px;
  margin-bottom: 20px;
  border: 1px solid #c3e6cb;
}

.toxicite-item {
  background: white;
  border: 1px solid #ddd;
  border-radius: 8px;
  padding: 15px;
  margin-bottom: 15px;
}

.toxicite-header {
  display: flex;
  justify-content: space-between;
  border-bottom: 1px solid #f0f0f0;
  padding-bottom: 10px;
  margin-bottom: 10px;
}

.desc {
  color: #666;
  font-size: 0.9rem;
  margin-top: 5px;
}

.symptomes-section h5 {
  margin: 0 0 10px 0;
  color: #2c3e50;
}

.symptomes-section ul {
  list-style: none;
  padding: 0;
}

.symptomes-section li {
  display: flex;
  justify-content: space-between;
  background: #f8f9fa;
  padding: 5px 10px;
  margin-bottom: 5px;
  border-radius: 4px;
  font-size: 0.85rem;
}

.btn-txt-del {
  color: #e74c3c;
  background: none;
  border: none;
  cursor: pointer;
  font-size: 0.75rem;
}

.btn-add-symptome {
  background: none;
  border: 1px dashed #3498db;
  color: #3498db;
  padding: 5px 10px;
  border-radius: 4px;
  cursor: pointer;
  margin-top: 10px;
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 100;
}

.modal-content {
  width: 350px;
  background: white;
}

.btn-back {
  background: #95a5a6;
  color: white;
  border: none;
  padding: 10px;
  border-radius: 4px;
}
</style>
