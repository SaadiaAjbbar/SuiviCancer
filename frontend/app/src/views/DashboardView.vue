<script setup>
import { ref, onMounted } from 'vue';

const token = localStorage.getItem('user_token');
const medecinData = JSON.parse(localStorage.getItem('user_info') || '{}');

const stats = ref({
  consultations: 0,
  patients: 0,
  questions: 0
});

const fetchData = async () => {
  try {
    const headers = { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' };
    const [resC, resP, resQ] = await Promise.all([
      fetch('http://localhost:8080/api/medecin/consultations', { headers }),
      fetch('http://localhost:8080/api/medecin/my-patients', { headers }),
      fetch('http://localhost:8080/api/medecin/questions', { headers })
    ]);

    const consultations = await resC.json();
    const patients = await resP.json();
    const questions = await resQ.json();

    stats.value = {
      consultations: consultations.length,
      patients: patients.length,
      questions: questions.length
    };
  } catch (e) {
    console.error("Erreur stats:", e);
  }
};

onMounted(() => fetchData());
</script>

<template>
  <div class="dashboard-page">
    <header class="welcome-header">
      <h1>Sbah lkhir, Dr. {{ medecinData.nom }} 👋</h1>
      <p>Ha chno tari l-youm f l-plateforme dyalk.</p>
    </header>

    <div class="stats-grid">
      <div class="stat-card blue">
        <div class="icon">📅</div>
        <div class="info">
          <h3>{{ stats.consultations }}</h3>
          <p>Consultations</p>
        </div>
      </div>

      <div class="stat-card green">
        <div class="icon">👥</div>
        <div class="info">
          <h3>{{ stats.patients }}</h3>
          <p>Patients Suivis</p>
        </div>
      </div>

      <div class="stat-card orange">
        <div class="icon">❓</div>
        <div class="info">
          <h3>{{ stats.questions }}</h3>
          <p>Questions FAQ</p>
        </div>
      </div>
    </div>

    </div>
</template>

<style scoped>
.welcome-header { margin-bottom: 2rem; }
.welcome-header h1 { color: #2c3e50; font-size: 1.8rem; }

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
}

.stat-card {
  background: white;
  padding: 1.5rem;
  border-radius: 12px;
  display: flex;
  align-items: center;
  gap: 1rem;
  box-shadow: 0 4px 6px rgba(0,0,0,0.05);
  border-bottom: 4px solid transparent;
}

.stat-card.blue { border-color: #3498db; }
.stat-card.green { border-color: #2ecc71; }
.stat-card.orange { border-color: #f39c12; }

.stat-card .icon {
  font-size: 2.5rem;
  background: #f8fafc;
  padding: 10px;
  border-radius: 10px;
}

.stat-card h3 { font-size: 2rem; margin: 0; color: #2c3e50; }
.stat-card p { margin: 0; color: #7f8c8d; font-weight: 500; }
</style>
