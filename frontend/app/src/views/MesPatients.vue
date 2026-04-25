<template>
  <div class="patients-container">
    <div class="page-header">
      <div class="header-content">
        <h1>👥 Mes Patients</h1>
        <p>Gérez et suivez l'état de santé de vos patients assignés.</p>
      </div>
      <div class="header-stats">
        <span class="stat-badge">{{ patients.length }} Patients au total</span>
      </div>
    </div>

    <div v-if="loading" class="loader-wrapper">
      <div class="spinner"></div>
      <p>Chargement des dossiers...</p>
    </div>

    <div v-else-if="errorMessage" class="error-card">
      <span class="error-icon">⚠️</span>
      <p>{{ errorMessage }}</p>
    </div>

    <div v-else class="table-card">
      <table class="custom-table">
        <thead>
          <tr>
            <th>Patient</th>
            <th>Contact</th>
            <th>Sexe</th>
            <th>Date Naissance</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="patient in patients" :key="patient.id" class="table-row">
            <td>
              <div class="patient-info">
                <div class="avatar-sm">
                  {{ patient.user.nom.charAt(0) }}{{ patient.user.prenom.charAt(0) }}
                </div>
                <div class="name-wrapper">
                  <span class="full-name">{{ patient.user.nom }} {{ patient.user.prenom }}</span>
                  <span class="role-label">Patient ID: #{{ patient.id }}</span>
                </div>
              </div>
            </td>
            <td>
              <div class="contact-info">
                <div class="info-item">📧 {{ patient.user.email }}</div>
                <div class="info-item">📞 {{ patient.telephone }}</div>
              </div>
            </td>
            <td>
              <span :class="['gender-pill', patient.sexe.toLowerCase()]">
                {{ patient.sexe }}
              </span>
            </td>
            <td>📅 {{ new Date(patient.date_naissance).toLocaleDateString() }}</td>

          </tr>
          <tr v-if="patients.length === 0">
            <td colspan="5" class="empty-state">
              <div class="empty-msg">
                <span>📭</span>
                <p>Aucun patient trouvé dans votre liste.</p>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const patients = ref([]);
const loading = ref(true);
const errorMessage = ref('');

onMounted(async () => {
  const token = localStorage.getItem('user_token');
  try {
    const response = await axios.get('http://localhost:8080/api/medecin/my-patients', {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    });
    patients.value = response.data;
  } catch (error) {
    if (error.response?.status === 401) {
      errorMessage.value = "Session expirée. Veuillez vous reconnecter.";
    } else {
      errorMessage.value = "Erreur lors de la récupération des patients.";
    }
  } finally {
    loading.value = false;
  }
});
</script>

<style scoped>
.patients-container {
  max-width: 1200px;
  margin: 0 auto;
}

/* Header */
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.page-header h1 {
  font-size: 1.8rem;
  color: #1e293b;
  margin-bottom: 0.5rem;
}

.page-header p {
  color: #64748b;
}

.stat-badge {
  background: #3b82f6;
  color: white;
  padding: 8px 16px;
  border-radius: 20px;
  font-weight: 600;
  font-size: 0.9rem;
}

/* Table Card */
.table-card {
  background: white;
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
  overflow: hidden;
  border: 1px solid #e2e8f0;
}

.custom-table {
  width: 100%;
  border-collapse: collapse;
}

.custom-table th {
  background: #f8fafc;
  padding: 1rem 1.5rem;
  text-align: left;
  font-size: 0.85rem;
  text-transform: uppercase;
  color: #64748b;
  letter-spacing: 0.05em;
  border-bottom: 1px solid #e2e8f0;
}

.table-row {
  transition: background 0.2s;
  border-bottom: 1px solid #f1f5f9;
}

.table-row:hover {
  background: #f8fafc;
}

.table-row td {
  padding: 1.2rem 1.5rem;
  color: #334155;
  vertical-align: middle;
}

/* Patient Info Cell */
.patient-info {
  display: flex;
  align-items: center;
  gap: 12px;
}

.avatar-sm {
  width: 40px;
  height: 40px;
  background: #eff6ff;
  color: #3b82f6;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 10px;
  font-weight: bold;
  font-size: 0.9rem;
}

.name-wrapper {
  display: flex;
  flex-direction: column;
}

.full-name {
  font-weight: 600;
  color: #1e293b;
}

.role-label {
  font-size: 0.75rem;
  color: #94a3b8;
}

/* Gender Pills */
.gender-pill {
  padding: 4px 12px;
  border-radius: 12px;
  font-size: 0.8rem;
  font-weight: 600;
}

.gender-pill.homme {
  background: #dcfce7;
  color: #15803d;
}

.gender-pill.femme {
  background: #fce7f3;
  color: #be185d;
}

/* Contact Items */
.info-item {
  font-size: 0.85rem;
  color: #64748b;
  margin-bottom: 2px;
}





/* States */
.loader-wrapper {
  text-align: center;
  padding: 4rem;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 4px solid #f3f3f3;
  border-top: 4px solid #3b82f6;
  border-radius: 50%;
  margin: 0 auto 1rem;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% {
    transform: rotate(0deg);
  }

  100% {
    transform: rotate(360deg);
  }
}

.error-card {
  background: #fef2f2;
  border-left: 4px solid #ef4444;
  padding: 1.5rem;
  border-radius: 8px;
  color: #b91c1c;
  display: flex;
  align-items: center;
  gap: 1rem;
}

.empty-state {
  text-align: center;
  padding: 3rem;
  color: #94a3b8;
}

.empty-msg span {
  font-size: 2.5rem;
}
</style>
