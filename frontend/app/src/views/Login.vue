<template>
  <div class="login-page">
    <div class="login-card">
      <h2>Connexion</h2>
      <p class="subtitle">Entrez vos accès pour continuer</p>

      <form @submit.prevent="login">

        <div class="input-group">
          <label>Email</label>
          <input v-model="email" type="email" placeholder="exemple@mail.com" required>
        </div>

        <div class="input-group">
          <label>Mot de passe</label>
          <input v-model="mot_de_passe" type="password" placeholder="••••••••" required>
        </div>

        <p v-if="errorMessage" class="error-text">{{ errorMessage }}</p>

        <button type="submit" :disabled="isLoading">
          {{ isLoading ? 'Chargement...' : 'Se connecter' }}
        </button>

      </form>
    </div>
  </div>

</template>
<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();

// 1. Déclaration des variables (Réactivité)
const email = ref('');
const mot_de_passe = ref('');
const errorMessage = ref('');
const isLoading = ref(false);

// 2. La fonction pour appeler l'API
const login = async () => {
  isLoading.value = true;
  errorMessage.value = '';

  try {
    const response = await fetch('http://localhost:8080/api/login', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: JSON.stringify({
        email: email.value,
        mot_de_passe: mot_de_passe.value,
      }),
    });

    const data = await response.json();

    if (response.ok) {
      // 1. On stocke les infos
      localStorage.setItem('user_token', data.token);
      localStorage.setItem('user_data', JSON.stringify(data.user));

      // 2. DEBUG : Regarde exactement ce qu'il y a dans data.user
      console.log("Utilisateur connecté :", data.user);

      // 3. Redirection Logique
      // ATTENTION : Vérifie si c'est 'ADMINGLOBAL' ou 'admin_global' dans ta base de données
      if (data.user && data.user.role === 'ADMINGLOBAL') {
        console.log("Redirection vers creer-hopital...");
        router.push('/creer-hopital');
      } else if (data.user && data.user.role === 'ADMINHOPITAL') {
        router.push('/admin-hopital-dashboard');
      } else if (data.user && data.user.role === 'INFIRMIERE') {
        router.push('/dashboard-infirmier');
      } else if (data.user.role === 'MEDECIN') {
        router.push('/medecin');
      } else if (data.user.role === 'PATIENT') {
        router.push('/patient');
      }
      else {
        console.log("Redirection vers l'accueil...");
        router.push('/');
      }
    }
  } catch (error) {
    errorMessage.value = "Erreur réseau";
  } finally {
    isLoading.value = false;
  }
};
</script>



<style scoped>
/* Un style simple et propre */
.login-page {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 80vh;
}

.login-card {
  background: white;
  padding: 2rem;
  border-radius: 12px;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
  width: 100%;
  max-width: 400px;
}

h2 {
  margin-bottom: 0.5rem;
  color: #333;
}

.subtitle {
  color: #666;
  margin-bottom: 1.5rem;
  font-size: 0.9rem;
}

.input-group {
  margin-bottom: 1.2rem;
  text-align: left;
}

.input-group label {
  display: block;
  margin-bottom: 0.4rem;
  font-weight: bold;
}

input {
  width: 100%;
  padding: 0.8rem;
  border: 1px solid #ddd;
  border-radius: 6px;
  box-sizing: border-box;
  /* Important pour que le padding ne casse pas la largeur */
}

.error-text {
  color: #e74c3c;
  background: #fdeaea;
  padding: 0.5rem;
  border-radius: 4px;
  font-size: 0.85rem;
}

button {
  width: 100%;
  padding: 0.8rem;
  background-color: #42b883;
  color: white;
  border: none;
  border-radius: 6px;
  font-weight: bold;
  cursor: pointer;
  transition: background 0.3s;
}

button:hover {
  background-color: #3aa876;
}

button:disabled {
  background-color: #a8d5c2;
  cursor: not-allowed;
}
</style>
