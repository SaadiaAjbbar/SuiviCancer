<template>
  <div class="advice-page">
    <header class="page-header">
      <div class="header-content">
        <h2>💡 Mes Conseils & Suivi</h2>
        <p>Retrouvez ici les recommandations et les réponses de vos médecins.</p>
      </div>
    </header>

    <div v-if="isLoading" class="loading-container">
      <div class="spinner"></div>
      <p>Chargement de vos données médicales...</p>
    </div>

    <div v-else-if="conseils.length > 0" class="advice-grid">
      <div v-for="c in conseils" :key="c.id" class="advice-card">
        <div class="advice-content">
          <div class="advice-header">
            <span class="category-tag">Conseil Médical</span>
            <span class="advice-date">{{ formatDate(c.created_at) }}</span>
          </div>

          <div class="advice-body">
            <p>{{ c.description }}</p>
          </div>

          <div v-if="c.etatGeneral?.reponses && c.etatGeneral.reponses.length > 0" class="responses-box">
            <h4 class="responses-title">💬 Réponses du médecin :</h4>
            <div v-for="rep in c.etatGeneral.reponses" :key="rep.id" class="response-item">
              <p class="response-text">{{ rep.message }}</p>
              <div class="response-meta" v-if="rep.medecin?.user">
                <span>Par Dr. {{ rep.medecin.user.nom }}</span>
              </div>
            </div>
          </div>

          <div class="advice-footer" v-if="c.etatGeneral?.consultation?.medecin?.user">
            <div class="doc-badge">
              <span class="icon">👨‍⚕️</span>
              <span class="doc-info">
                Dr. {{ c.etatGeneral.consultation.medecin.user.nom }} (Consultation)
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-else class="empty-state">
      <div class="empty-icon">📂</div>
      <h3>Aucun conseil disponible</h3>
      <p>Vous n'avez pas encore reçu de recommandations de la part de vos médecins.</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const conseils = ref([]);
const isLoading = ref(true);
const token = localStorage.getItem('user_token');

const fetchConseils = async () => {
  try {
    const res = await fetch('http://localhost:8080/api/patient/my-conseils', {
      method: 'GET',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      }
    });

    if (res.ok) {
      const data = await res.json();
      console.log("Données reçues du Backend:", data);
      conseils.value = data;
    } else {
      console.error("Erreur lors de la récupération");
    }
  } catch (e) {
    console.error("Erreur Connexion API:", e);
  } finally {
    isLoading.value = false;
  }
};

const formatDate = (dateStr) => {
  if (!dateStr) return "";
  return new Date(dateStr).toLocaleDateString('fr-FR', {
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  });
};

onMounted(fetchConseils);
</script>

<style scoped>
.advice-page {
  max-width: 1000px;
  margin: 40px auto;
  padding: 0 20px;
  font-family: 'Inter', sans-serif;
}

.page-header {
  margin-bottom: 40px;
  border-left: 5px solid #f1c40f;
  padding-left: 20px;
}

.page-header h2 {
  font-size: 1.8rem;
  color: #1e293b;
  margin: 0;
}

.page-header p {
  color: #64748b;
  margin-top: 5px;
}

.advice-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
  gap: 25px;
}

.advice-card {
  background: #ffffff;
  border-radius: 16px;
  padding: 25px;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
  border: 1px solid #e2e8f0;
  display: flex;
  flex-direction: column;
  transition: transform 0.2s ease;
}

.advice-card:hover {
  transform: translateY(-5px);
}

.advice-header {
  display: flex;
  justify-content: space-between;
  margin-bottom: 20px;
}

.category-tag {
  background: #fef9c3;
  color: #854d0e;
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
}

.advice-date {
  font-size: 0.85rem;
  color: #94a3b8;
}

.advice-body p {
  font-size: 1.1rem;
  color: #334155;
  line-height: 1.6;
  margin-bottom: 20px;
}

/* Section Réponses style Post-it ou Alert */
.responses-box {
  background: #f0fdf4;
  border-radius: 12px;
  padding: 15px;
  margin-bottom: 20px;
  border-left: 4px solid #22c55e;
}

.responses-title {
  font-size: 0.9rem;
  color: #166534;
  margin-bottom: 10px;
  font-weight: 600;
}

.response-item {
  border-bottom: 1px solid #dcfce7;
  padding-bottom: 8px;
  margin-bottom: 8px;
}

.response-item:last-child {
  border-bottom: none;
}

.response-text {
  font-size: 0.95rem;
  color: #14532d;
  margin: 0;
}

.response-meta {
  font-size: 0.8rem;
  color: #15803d;
  font-style: italic;
  margin-top: 4px;
}

.advice-footer {
  margin-top: auto;
  padding-top: 15px;
  border-top: 1px solid #f1f5f9;
}

.doc-badge {
  display: flex;
  align-items: center;
  gap: 8px;
}

.doc-info {
  font-size: 0.85rem;
  color: #475569;
  font-weight: 500;
}

.loading-container, .empty-state {
  text-align: center;
  padding: 100px 20px;
  background: white;
  border-radius: 20px;
  color: #64748b;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 4px solid #f1f5f9;
  border-top: 4px solid #f1c40f;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 20px;
}

@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

@media (max-width: 640px) {
  .advice-grid { grid-template-columns: 1fr; }
}
</style>
