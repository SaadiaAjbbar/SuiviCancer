<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';

const token = localStorage.getItem('user_token');
const currentTab = ref('medecins');
const listData = ref([]);
const isLoading = ref(false);
const messageSuccess = ref('');
const router = useRouter();

// --- FORMULAIRES ---
const formMedecin = ref({ nom: '', prenom: '', email: '', password: '', specialite: '' });
const formInfermier = ref({ nom: '', prenom: '', email: '', mot_de_passe: '' });
const formToxicite = ref({ nom: '', description: '', symptomes: [] });

const isEditing = ref(false);
const editId = ref(null);

// Gestion de l'ajout rapide de symptômes dans le formulaire
const showSymptomeForm = ref(false);
const tempSymptome = ref({ nom: '', description: '' });

const logout = () => {
  localStorage.removeItem('user_token');
  router.push('/login');
};

// --- CHARGEMENT DES DONNÉES ---
const fetchData = async (endpoint) => {
  // التغيير هنا: كنزيـدو /hopital/ غير للـ toxicites
  let apiPath = endpoint === 'toxicites' ? 'hopital/toxicites' : endpoint;

  try {
    const response = await fetch(`http://localhost:8080/api/${apiPath}`, {
      headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
    });
    const result = await response.json();
    listData.value = Array.isArray(result) ? result : [];
  } catch (e) {
    console.error("Erreur", e);
  }
};
// --- CRUD MÉDECINS & INFIRMIERS ---
const saveMedecin = async () => {
  const method = isEditing.value ? 'PUT' : 'POST';
  const url = isEditing.value ? `http://localhost:8080/api/medecins/${editId.value}` : 'http://localhost:8080/api/medecins';
  const response = await fetch(url, {
    method,
    headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' },
    body: JSON.stringify(formMedecin.value)
  });
  if (response.ok) { messageSuccess.value = "Médecin enregistré !"; resetForm(); fetchData('medecins'); }
};

const saveInfermier = async () => {
  const method = isEditing.value ? 'PUT' : 'POST';
  const url = isEditing.value ? `http://localhost:8080/api/infermiers/${editId.value}` : 'http://localhost:8080/api/infermiers';
  const response = await fetch(url, {
    method,
    headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' },
    body: JSON.stringify(formInfermier.value)
  });
  if (response.ok) { messageSuccess.value = "Infirmier(e) enregistré(e) !"; resetForm(); fetchData('infermiers'); }
};

// --- CRUD TOXICITÉS (MODIFIÉ AVEC LISTE) ---

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

  const url = isEditing.value
    ? `http://localhost:8080/api/hopital/toxicites/${editId.value}`
    : 'http://localhost:8080/api/hopital/toxicites';

  // ... باقي الكود كيبقى هو هو
  const response = await fetch(url, {
    method,
    headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' },
    body: JSON.stringify({ nom: formToxicite.value.nom, description: formToxicite.value.description })
  });

  if (response.ok) {
    const result = await response.json();
    const toxiciteId = isEditing.value ? editId.value : (result.data?.id || result.id);

    // Sauvegarde des nouveaux symptômes attachés
    for (const symp of formToxicite.value.symptomes) {
      if (!symp.id) {
        await fetch('http://localhost:8080/api/symptomes', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${token}` },
          body: JSON.stringify({ ...symp, toxicite_id: toxiciteId })
        });
      }
    }
    messageSuccess.value = isEditing.value ? "Toxicité mise à jour !" : "Toxicité ajoutée !";
    resetForm();
    fetchData('hopital/toxicites');
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
  fetchData('/hopital/toxicites');
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
  <div class="admin-dashboard">
    <header class="dashboard-header">
      <div class="header-main">
        <h1>Dashboard Admin Hôpital</h1>
        <button @click="logout" class="btn-logout">logout <i class="fas fa-sign-out-alt"></i></button>
      </div>
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
              <button
                @click="isEditing = true; editId = inf.id; formInfermier = { ...inf.user, mot_de_passe: '' }">Editer</button>
              <button @click="deleteItem('infermiers', inf.id)" class="btn-del">X</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="currentTab === 'toxicites'">
      <section class="card">
        <h3>{{ isEditing ? 'Modifier' : 'Ajouter' }} une Toxicité</h3>
        <form @submit.prevent="saveToxicite">
          <div class="form-grid" style="margin-bottom: 15px;">
            <input v-model="formToxicite.nom" placeholder="Nom (ex: Anémie)" required>
            <input v-model="formToxicite.description" placeholder="Description">
          </div>

          <div class="symptome-section-form">
            <label>Symptômes associés :</label>
            <div class="tags-container">
              <span v-for="(s, index) in formToxicite.symptomes" :key="index" class="s-tag">
                {{ s.nom }}
                <button type="button" @click="s.id ? deleteSymptome(s.id) : removeSymptomeFromForm(index)">x</button>
              </span>
              <button type="button" @click="showSymptomeForm = true" class="btn-add-pill">+ Ajouter</button>
            </div>
          </div>

          <div style="margin-top: 15px;">
            <button type="submit" class="btn-save">{{ isEditing ? 'Mettre à jour' : 'Enregistrer' }}</button>
            <button v-if="isEditing" type="button" @click="resetForm" class="btn-back"
              style="margin-left: 10px;">Annuler</button>
          </div>
        </form>
      </section>

      <div v-if="showSymptomeForm" class="modal-overlay">
        <div class="card modal-content">
          <h4>Nouveau symptôme</h4>
          <input v-model="tempSymptome.nom" placeholder="Nom du symptôme" class="modal-input">
          <textarea v-model="tempSymptome.description" placeholder="Description" class="modal-input"></textarea>
          <div class="actions">
            <button @click="addSymptomeToForm" class="btn-save">Ajouter</button>
            <button @click="showSymptomeForm = false" class="btn-back">Fermer</button>
          </div>
        </div>
      </div>

      <table class="toxicite-table">
        <thead>
          <tr>
            <th>Nom</th>
            <th>Description</th>
            <th>Symptômes</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="t in listData" :key="t.id">
            <td><strong>{{ t.nom }}</strong></td>
            <td>{{ t.description }}</td>
            <td>
              <div class="pills-list">
                <span v-for="s in t.symptomes" :key="s.id" class="mini-pill">{{ s.nom }}</span>
              </div>
            </td>
            <td>
              <button @click="preparerEditToxicite(t)" class="btn-edit-sml">Modifier</button>

              <button @click="deleteToxicite(t.id)" class="btn-del-sml">Supprimer</button>
            </td>
          </tr>
          <tr v-if="!listData.length">
            <td colspan="4" style="text-align: center;">Aucune toxicité trouvée</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<style scoped>
/* STYLES GÉNÉRAUX */
.admin-dashboard {
  padding: 20px;
  max-width: 1100px;
  margin: auto;
  font-family: sans-serif;
}

.header-main {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.btn-logout {
  background-color: #ff4757;
  color: white;
  border: none;
  padding: 8px 16px;
  border-radius: 6px;
  cursor: pointer;
  font-weight: bold;
}

.tabs {
  margin-bottom: 20px;
  border-bottom: 2px solid #eee;
  display: flex;
  gap: 10px;
  padding-bottom: 10px;
}

.tabs button {
  padding: 10px 25px;
  border: none;
  cursor: pointer;
  background: #f1f2f6;
  border-radius: 6px;
  font-weight: bold;
  transition: 0.3s;
}

.tabs button.active {
  background: #3498db;
  color: white;
}

.card {
  background: #f9f9f9;
  padding: 20px;
  border-radius: 10px;
  border: 1px solid #ddd;
  margin-bottom: 30px;
}

.form-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.form-grid input {
  padding: 12px;
  border: 1px solid #ccc;
  border-radius: 6px;
  flex: 1;
  min-width: 200px;
}

table {
  width: 100%;
  border-collapse: collapse;
  background: white;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

th,
td {
  border: 1px solid #eee;
  padding: 15px;
  text-align: left;
}

th {
  background: #f4f4f4;
  color: #333;
}

.btn-save {
  background: #27ae60;
  color: white;
  border: none;
  padding: 12px 25px;
  border-radius: 6px;
  cursor: pointer;
  font-weight: bold;
}

.btn-del {
  background: #e74c3c;
  color: white;
  border: none;
  padding: 6px 12px;
  border-radius: 4px;
  cursor: pointer;
}

.alert {
  background: #d4edda;
  color: #155724;
  padding: 15px;
  border-radius: 8px;
  margin-bottom: 20px;
  border: 1px solid #c3e6cb;
}

/* SPÉCIFIQUE TOXICITÉ */
.tags-container {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-top: 10px;
  border: 1px dashed #bbb;
  padding: 10px;
  border-radius: 8px;
  background: #fff;
}

.s-tag {
  background: #e1f5fe;
  color: #0288d1;
  padding: 5px 12px;
  border-radius: 20px;
  font-size: 0.85rem;
  display: flex;
  align-items: center;
  gap: 8px;
}

.s-tag button {
  background: none;
  border: none;
  color: #e74c3c;
  cursor: pointer;
  font-weight: bold;
  font-size: 1rem;
}

.btn-add-pill {
  background: #f1f2f6;
  border: 1px solid #ccc;
  padding: 5px 15px;
  border-radius: 20px;
  cursor: pointer;
}

.mini-pill {
  background: #f0f0f0;
  padding: 3px 8px;
  border-radius: 4px;
  margin-right: 4px;
  font-size: 0.75rem;
  color: #666;
  display: inline-block;
}

.btn-edit-sml {
  background: #f39c12;
  color: white;
  border: none;
  padding: 6px 12px;
  border-radius: 4px;
  cursor: pointer;
  margin-right: 5px;
}

.btn-del-sml {
  background: #e74c3c;
  color: white;
  border: none;
  padding: 6px 12px;
  border-radius: 4px;
  cursor: pointer;
}

/* MODAL */
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
  z-index: 1000;
}

.modal-content {
  width: 400px;
  background: white;
  padding: 25px;
  border-radius: 12px;
}

.modal-input {
  width: 100%;
  padding: 12px;
  margin-bottom: 12px;
  border: 1px solid #ddd;
  border-radius: 6px;
  box-sizing: border-box;
}

.btn-back {
  background: #95a5a6;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 6px;
  cursor: pointer;
}

/* REPRISE EXACTE DE TES STYLES */
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

/* STYLES TOXICITÉ */
.symptome-box {
  background: white;
  padding: 10px;
  border: 1px dashed #ccc;
  border-radius: 5px;
}

.tags-list {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-top: 5px;
}

.s-tag {
  background: #e1f5fe;
  color: #0288d1;
  padding: 4px 10px;
  border-radius: 15px;
  font-size: 0.8rem;
  display: flex;
  align-items: center;
  gap: 5px;
}

.s-tag button {
  background: none;
  border: none;
  color: #e74c3c;
  cursor: pointer;
  font-weight: bold;
}

.btn-add-tag {
  background: #f1f2f6;
  border: 1px solid #ddd;
  padding: 4px 12px;
  border-radius: 15px;
  cursor: pointer;
}

.toxicite-item {
  background: white;
  border: 1px solid #ddd;
  border-radius: 8px;
  padding: 15px;
  margin-bottom: 10px;
}

.toxicite-header {
  display: flex;
  justify-content: space-between;
  border-bottom: 1px solid #f9f9f9;
  padding-bottom: 5px;
}

.btns {
  display: flex;
  gap: 5px;
}

.btn-edit-sml {
  background: #f39c12;
  color: white;
  border: none;
  padding: 4px 8px;
  border-radius: 4px;
  cursor: pointer;
}

.btn-del-sml {
  background: #e74c3c;
  color: white;
  border: none;
  padding: 4px 8px;
  border-radius: 4px;
  cursor: pointer;
}

.pills-container {
  margin-top: 8px;
}

.pill {
  background: #eee;
  padding: 2px 8px;
  border-radius: 4px;
  margin-right: 5px;
  font-size: 0.75rem;
  color: #555;
}

/* MODAL & LOGOUT */
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
  padding: 20px;
}

.modal-input {
  width: 100%;
  padding: 10px;
  margin-bottom: 10px;
  border: 1px solid #ddd;
  border-radius: 4px;
}

.header-main {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.btn-logout {
  background-color: #ff4757;
  color: white;
  border: none;
  padding: 8px 16px;
  border-radius: 6px;
  cursor: pointer;
  font-weight: bold;
  display: flex;
  align-items: center;
  gap: 8px;
}

.btn-back {
  background: #95a5a6;
  color: white;
  border: none;
  padding: 10px;
  border-radius: 4px;
  cursor: pointer;
}

/* TES STYLES PRÉCÉDENTS RÉUTILISÉS */
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

/* STYLES TOXICITÉ & SYMPTÔMES */
.symptome-multi-box {
  background: #fff;
  border: 1px dashed #bbb;
  padding: 10px;
  border-radius: 5px;
}

.tags-container {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-top: 10px;
}

.symptome-tag {
  background: #e1f5fe;
  color: #0288d1;
  padding: 5px 12px;
  border-radius: 20px;
  font-size: 0.85rem;
  display: flex;
  align-items: center;
  gap: 8px;
}

.symptome-tag button {
  background: none;
  border: none;
  color: #e74c3c;
  cursor: pointer;
  font-weight: bold;
}

.btn-add-symptome-tag {
  background: #eee;
  border: 1px solid #ccc;
  border-radius: 20px;
  padding: 5px 12px;
  cursor: pointer;
  font-size: 0.85rem;
}

.symptomes-pills {
  display: flex;
  flex-wrap: wrap;
  gap: 5px;
  margin-top: 10px;
}

.pill {
  background: #f1f1f1;
  padding: 2px 8px;
  border-radius: 4px;
  font-size: 0.8rem;
  color: #666;
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
}

/* MODAL & LOGOUT */
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
  width: 400px;
  background: white;
  padding: 25px;
}

.modal-input {
  width: 100%;
  padding: 10px;
  margin-bottom: 10px;
  border: 1px solid #ddd;
  border-radius: 4px;
}

.header-main {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.btn-logout {
  background-color: #ff4757;
  color: white;
  border: none;
  padding: 8px 16px;
  border-radius: 6px;
  cursor: pointer;
  font-weight: bold;
  display: flex;
  align-items: center;
  gap: 8px;
}

.btn-back {
  background: #95a5a6;
  color: white;
  border: none;
  padding: 10px;
  border-radius: 4px;
  cursor: pointer;
}

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

.header-main {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.btn-logout {
  background-color: #ff4757;
  /* لون أحمر هادئ */
  color: white;
  border: none;
  padding: 8px 16px;
  border-radius: 6px;
  cursor: pointer;
  font-weight: bold;
  transition: background 0.3s ease;
  display: flex;
  align-items: center;
  gap: 8px;
}

.btn-logout:hover {
  background-color: #ff6b81;
  box-shadow: 0 2px 8px rgba(255, 71, 87, 0.3);
}

/* تعديل بسيط للهيدر ليتناسب مع الزر */
.dashboard-header {
  border-bottom: 2px solid #eee;
  margin-bottom: 20px;
}
</style>
