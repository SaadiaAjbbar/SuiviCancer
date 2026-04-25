<template>
  <div class="min-h-screen flex items-center justify-center bg-slate-50 px-4 py-12">
    <div class="w-full max-w-md">
      <div class="text-center mb-10">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-primary/10 text-primary rounded-2xl mb-4">
          <span class="text-3xl">🏥</span>
        </div>
        <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Bienvenue</h1>
        <p class="mt-2 text-slate-600">Connectez-vous pour accéder à votre espace santé</p>
      </div>

      <AppCard class="shadow-xl shadow-slate-200/60 border-slate-200/60">
        <form @submit.prevent="login" class="space-y-6">
          <AppInput
            v-model="email"
            label="Email"
            type="email"
            placeholder="votre@email.com"
            required
          />

          <AppInput
            v-model="mot_de_passe"
            label="Mot de passe"
            type="password"
            placeholder="••••••••"
            required
          />

          <div v-if="errorMessage" class="p-3 bg-red-50 border border-red-100 text-red-600 text-sm rounded-lg flex items-center gap-2">
            <span>⚠️</span> {{ errorMessage }}
          </div>

          <AppButton
            type="submit"
            :loading="isLoading"
            custom-class="w-full"
            size="lg"
          >
            Se connecter
          </AppButton>
        </form>

        <template #footer>
          <p class="text-center text-sm text-slate-500">
            Besoin d'aide ? <a href="#" class="text-primary font-semibold hover:underline">Contactez le support</a>
          </p>
        </template>
      </AppCard>

      <p class="mt-8 text-center text-xs text-slate-400">
        &copy; 2026 E-Sante. Tous droits réservés.
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import AppButton from '@/components/ui/AppButton.vue';
import AppInput from '@/components/ui/AppInput.vue';
import AppCard from '@/components/ui/AppCard.vue';

const router = useRouter();

const email = ref('');
const mot_de_passe = ref('');
const errorMessage = ref('');
const isLoading = ref(false);

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
      localStorage.setItem('user_token', data.token);
      localStorage.setItem('user_data', JSON.stringify(data.user));

      console.log("Utilisateur connecté :", data.user);

      if (data.user && data.user.role === 'ADMINGLOBAL') {
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
        router.push('/');
      }
    } else {
      errorMessage.value = data.message || "Identifiants incorrects";
    }
  } catch (error) {
    errorMessage.value = "Erreur réseau";
  } finally {
    isLoading.value = false;
  }
};
</script>
