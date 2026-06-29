// js/auth.js
const Auth = (() => {
  // Retorna o token armazenado
  const getToken = () => localStorage.getItem('token');

  // Retorna os dados do usuário salvos no login
  const getUsuario = () => {
    const u = localStorage.getItem('usuario');
    return u ? JSON.parse(u) : null;
  };

  // Verifica se há um token (usuário logado)
  const isLogged = () => !!getToken();

  // Salva token e dados do usuário
  const setSession = (token, usuario) => {
    localStorage.setItem('token', token);
    localStorage.setItem('usuario', JSON.stringify(usuario));
  };

  // Remove token e dados (logout)
  const logout = () => {
    localStorage.removeItem('token');
    localStorage.removeItem('usuario');
    window.location.href = 'index.html';
  };

  // Redireciona para login se não estiver autenticado
  const requireAuth = () => {
    if (!isLogged()) {
      window.location.href = 'login.html';
    }
  };

  // Retorna headers com Authorization para fetch
  const authHeaders = () => ({
    'Content-Type': 'application/json',
    'Authorization': `Bearer ${getToken()}`
  });

  return { getToken, getUsuario, isLogged, setSession, logout, requireAuth, authHeaders };
})();