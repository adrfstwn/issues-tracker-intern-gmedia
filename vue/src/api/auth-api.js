import api from "./api";

import { ref } from "vue";

import { authServices } from "@/services/auth-services";

export const loading = ref(false);
export const errors = ref({});

// login api
export const login = async (credentials) => {
  try {
    if (!credentials.email || !credentials.password) {
      errors.value = {
        email: !credentials.email
          ? ["The email field is required."]
          : undefined,
        password: !credentials.password
          ? ["The password field is required."]
          : undefined,
      };
      throw new Error("Validation failed");
    }
    const response = await api.post("/login", credentials);
    if (response.data.token) {
      authServices.setToken(response.data.token);
      authServices.setUserId(response.data.id);
    }
    return response.data;
  } catch (error) {
    console.error("Login error:", error.response?.data || error.message);
    throw error;
  }
};

// register api
export const register = async (userData) => {
  try {
    const response = await api.post("/register", userData);
    if (response.data.token) {
      authServices.setToken(response.data.token);
    }
    return response.data;
  } catch (error) {
    console.error("Register error:", error.response?.data || error.message);
    throw error;
  }
};

// logout api
export const logout = async () => {
  try {
    await api.post("/logout");
    authServices.removeToken();
  } catch (error) {
    console.error("Logout error:", error.response?.data || error.message);
    throw error;
  }
};

// user api
export const fetchUsers = async () => {
  try {
    const response = await api.get("/users");
    return response.data;
  } catch (error) {
    console.error("Failed to fetch user data", error);
    if (error.response) {
      console.error("Response data:", error.response.data);
      notification.value =
        "Unable to fetch user data: " + error.response.data.message;
    } else {
      notification.value = "Unable to fetch user data.";
    }
  }
};

// user by id api
export const getUserById = async (id) => api.get(`/user/${id}`);

// delete user api
export const deleteUserId = async (id) => {
  try {
    const response = await api.delete(`/user/${id}`);
    return response.data; 
  } catch (error) {
    console.error("Delete user error:", error.response?.data || error.message);
    throw error; 
  }
};

// update user api
export const updateUser = async (id, userData) => {
  try {
    const response = await api.put(`/user/${id}`, userData);
    return response.data;
  } catch (error) {
    console.error("Update user error:", error.response?.data || error.message);
    throw error;
  }
};

// user profile api
export const userProfile = async () => {
  try {
    const response = await api.get("/user");
    return response.data; 
  } catch (error) {
    console.error("Failed to fetch user profile:", error);
    throw error; 
  }
};

// update profile api
export const updateProfile = async (userData) => {
  try {
    const response = await api.put("/user", userData);
    return response.data; 
  } catch (error) {
    console.error("Update profile error:", error);
    throw error; 
  }
};

// current user api
export const currentUserApi = () => api.get("/user");

// permissionapi
// export const permissionApi = () => api.get("/permission");

// permission api
export const permissionApi = async () => {
  try {
    const response = await api.get("/permission");
    console.log("Permission API Response:", response.data); 
    return response.data; // Ubah dari return response; menjadi return response.data untuk memastikan data yang benar
  } catch (error) {
    console.error("Failed to fetch permissions:", error);
    throw error; 
  }
};