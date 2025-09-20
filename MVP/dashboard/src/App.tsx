import './App.css';
import { Admin, CustomRoutes, EditGuesser, ListGuesser, Resource, ShowGuesser } from 'react-admin';
import { Route } from 'react-router-dom';

import dataProvider from './dataProvider/dataProvider';
import Layout from './layout/Layout';
import { authProvider } from './authProvider/authProvider';
import ConfiguracaoUsuario from './components/users/Configuracao';
import { LoginPage } from './components/LoginPage';

import { i18nProvider, myTheme } from './theme';
import UserCreate from './components/users/UsuarioCreate';
import { UsuarioList } from './components/users/UsuarioList';

function App() {
  return (
    <Admin
      layout={Layout}
      theme={myTheme}
      dataProvider={dataProvider}
      i18nProvider={i18nProvider}
      authProvider={authProvider}
      loginPage={LoginPage}

    >
      <CustomRoutes>
        <Route
          path="/configuracoes"
          element={<ConfiguracaoUsuario />}
        />
      </CustomRoutes>
      <Resource
        options={{ label: "Usuários" }}
        name="usuarios" 
        list={UsuarioList}
        edit={EditGuesser}
        show={ShowGuesser}
        create={UserCreate}
      />
      <Resource
        options={{ label: "Parceiros" }}
        name="parceiros" 
        list={ListGuesser}
        edit={EditGuesser}
        show={ShowGuesser}
        create={UserCreate}
      />
  
    </Admin>
  )
}

export default App
