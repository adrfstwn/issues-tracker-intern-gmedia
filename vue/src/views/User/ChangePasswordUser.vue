<script setup>
import DefaultLayout from "@/layout/DefaultLayout.vue";
import { ref, onMounted } from "vue";
import { useRouter} from "vue-router";
import { useAuthStore } from "@/stores/auth";
import showIcon from "@/assets/image/showps.png";
import hideIcon from "@/assets/image/hideps.png";

const authStore = useAuthStore();
const router = useRouter();
const loading = ref(true);
const isEditing = ref(false);
const post = ref({
  password: "",
  confirmPassword: "",
});

// Variabel untuk menyimpan notifikasi
const notification = ref('');
const isSuccess = ref(false); // Variabel untuk menentukan apakah notifikasi berhasil atau gagal

// Variabel untuk visibilitas password
const isPasswordVisible = ref({
  password: false,
  confirmPassword: false,
});

// Fungsi toggle visibilitas password
const togglePasswordVisibility = (field) => {
  isPasswordVisible.value[field] = !isPasswordVisible.value[field];
};

// Fungsi untuk generate password
const generatePassword = () => {
  const length = 12; // Panjang password
  const charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_+[]{}|;:,.<>?"; // Karakter yang digunakan untuk password
  let result = "";
  for (let i = 0; i < length; i++) {
    result += charset.charAt(Math.floor(Math.random() * charset.length));
  }
  post.value.password = result;
  post.value.confirmPassword = result; // Auto-fill confirm password dengan password yang dihasilkan
};

const store = async () => {
  if (post.password !== post.confirmPassword) {
    validation.value = { confirmPassword: ["Password do not match"] };
    return;
  }
};

// Fetch user data from the store
const getUsers = async (userId) => {
  try {
    await authStore.handleGetUserById(userId); // Pass userId to the function
  } catch (error) {
    console.error("Failed to fetch password user profile", error);
  } finally {
    loading.value = false;
  }
};

// Save user updates
const saveUserUpdates = async () => {
    notification.value = '';
    isSuccess.value = false; // Reset status success

    // Validate input
    if (post.value.password !== post.value.confirmPassword) {
      notification.value = 'New password and confirmation must match.';
      return;
    }

    try {
    const userData = {
        password: post.value.password,
        password_confirmation: post.value.confirmPassword,
    };
    await authStore.handleUpdateUser(authStore.user.id, userData); 
    isEditing.value = false;
    notification.value = 'Password updated successfully!';
    isSuccess.value = true;
  } catch (error) { 
    let errorMessage = 'An unexpected error occurred.';

    if (error.response && error.response.data) {
        const responseErrors = error.response.data.errors;
        if (responseErrors) {
            errorMessage = Object.values(responseErrors).flat().join(', '); // Combine all error messages into a single string
        } else if (error.response.data.message) {
            errorMessage = error.response.data.message;
        }
    }
    notification.value = 'Failed to update password: ' + errorMessage; 
    isSuccess.value = false; 
  }
};

// Call fetchUserData when component mounts
onMounted(() => {
  if (authStore.user) {
    getUsers(authStore.user.id); // Call getUsers with the user ID
  }
});

// Fungsi untuk tombol Cancel
const cancel = () => {
  router.push({ path: "/users-list" });
};
</script>

<template>
  <DefaultLayout class="bg-whiteBgPrimary-100">
    <div class="max-h-fit md:p-9 p-4 flex flex-col gap-6 bg-white rounded-2xl">
      <div class="flex flex-col gap-1">
        <h2 class="text-codgray-900 md:text-2xl text-base font-semibold">
          Change Password Account
        </h2>
        <p class="md:text-base text-sm text-wildsand-400">
          Please fill in this field to proceed with updating the user.
        </p>
      </div>
      <form @submit.prevent="saveUserUpdates" class="flex flex-col gap-6">
        
        <!-- New Password -->
        <div class="flex flex-col gap-2">
          <label class="text-sm md:text-base text-wildsand-600" for="password">
            New Password
          </label>
          <div class="flex items-center gap-2">
            <div class="relative w-full">
              <input
                class="w-full hover:border-cobalt-700 h-12 border border-wildsand-300 hover:bg-cobalt-50 focus:outline-none focus:ring-1 focus:ring-cobalt-700 text-codgray-900 rounded-md shadow-sm p-2 text-base placeholder-small pr-10"
                id="password"
                :type="isPasswordVisible.new ? 'text' : 'password'"
                v-model="post.password"
                placeholder="Enter new password"
              />
              <img
                :src="isPasswordVisible.new ? showIcon : hideIcon"
                @click="togglePasswordVisibility('new')"
                class="absolute top-1/2 right-3 transform -translate-y-1/2 cursor-pointer w-5 h-5"
                alt="Toggle password visibility"
              />
            </div>
            <button
              type="button"
              class="bg-cobalt-700 text-white rounded px-4 py-2 flex-shrink-0"
              @click="generatePassword"
            >
              Generate Password
            </button>
          </div>
        </div>

      <!-- Confirm Password -->
        <div class="flex flex-col gap-2">
          <label
            class="text-sm md:text-base text-wildsand-600 flex gap-1"
            for="confirm-password"
          >
            Confirm Password
          </label>
          <div class="relative">
          <input
            class="w-full hover:border-cobalt-700 h-12 border border-wildsand-300 hover:bg-cobalt-50 focus:outline-none focus:ring-1 focus:ring-cobalt-700 text-codgray-900 rounded-md shadow-sm p-2 text-base placeholder-small"
            id="confirm-password"
            :type="isPasswordVisible.confirmPassword ? 'text' : 'password'"
            v-model="post.confirmPassword"
            placeholder="Confirm new password"
          />
          <img
              :src="isPasswordVisible.confirmPassword ? showIcon : hideIcon"
              @click="togglePasswordVisibility('confirmPassword')"
              class="absolute top-1/2 right-3 transform -translate-y-1/2 cursor-pointer w-5 h-5"
              alt="Toggle password visibility"
          />
        </div>
      </div>

        <div class="flex gap-4 justify-end w-full">
          <button
            type="button"
            class="md:px-6 md:py-3 px-4 max-w-fit font-semibold py-2 bg-transparent text-cobalt-700 border-2 border-cobalt-700 rounded-xl w-36 hover:bg-gray-300"
            @click="cancel"
          >
            Cancel
          </button>
          <button
            type="submit"
            class="md:px-6 md:py-3 px-4 max-w-fit font-semibold py-2 bg-gradient-to-b from-cobalt-700 to-cobalt-900 text-white rounded-xl w-36"
          >
            Save
          </button>
        </div>

        <!-- Notifikasi -->
        <div
          v-if="notification"
          :class="`mt-4 p-4 rounded ${isSuccess ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'}`"
        >
          {{ notification }}
        </div>
      </form>
    </div>
  </DefaultLayout>
</template>