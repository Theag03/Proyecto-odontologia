// authHandler.js

class AuthHandler {
  constructor() {
    // Verificar autenticación al inicializar
    this.checkAuth();
  }

  // Método para verificar si el usuario está autenticado
  isAuthenticated() {
    // Aquí puedes implementar tu lógica de verificación de sesión
    // Por ejemplo, verificar un token en localStorage o una cookie
    const token = localStorage.getItem('authToken');
    const userSession = localStorage.getItem('userSession');
    
    return !!token && !!userSession; // Devuelve true si ambos existen
  }

  // Método para redirigir al registro si no está autenticado
  checkAuth() {
    if (!this.isAuthenticated()) {
      this.redirectToRegister();
    }
  }

  // Método para redirigir a la página de registro
  redirectToRegister() {
    // Guarda la URL actual para poder volver después del registro
    if (!window.location.pathname.includes('register')) {
      sessionStorage.setItem('redirectAfterLogin', window.location.href);
    }
    
    // Redirige al registro
    window.location.href = '/register'; // Ajusta la ruta según tu aplicación
  }

  // Método para manejar el inicio de sesión exitoso
  handleLoginSuccess(authData) {
    // Guarda los datos de autenticación
    localStorage.setItem('authToken', authData.token);
    localStorage.setItem('userSession', JSON.stringify(authData.user));
    
    // Redirige a la página original o a la de citas
    const redirectUrl = sessionStorage.getItem('redirectAfterLogin') || '/appointments';
    window.location.href = redirectUrl;
  }

  // Método para cerrar sesión
  logout() {
    // Elimina los datos de autenticación
    localStorage.removeItem('authToken');
    localStorage.removeItem('userSession');
    
    // Redirige al registro
    this.redirectToRegister();
  }
}

// Uso básico en tu aplicación:
const auth = new AuthHandler();

// Cuando el usuario complete el registro o inicio de sesión:
// auth.handleLoginSuccess({token: '...', user: {...}});