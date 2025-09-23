import './App.css';
import { Admin, CustomRoutes, ListGuesser, Resource, ShowGuesser } from 'react-admin';
import { Route } from 'react-router-dom';

import Layout from './layout/Layout';
import { authProvider } from './authProvider/authProvider';
import ConfiguracaoUsuario from './components/users/Configuracao';
import { LoginPage } from './components/LoginPage';
import PersonIcon from '@mui/icons-material/Person';
import Groups2Icon from '@mui/icons-material/Groups2';
import PaymentsRoundedIcon from '@mui/icons-material/PaymentsRounded';
import DescriptionOutlinedIcon from '@mui/icons-material/DescriptionOutlined';
import NightShelterOutlinedIcon from '@mui/icons-material/NightShelterOutlined';

import { i18nProvider, myTheme } from './theme';
import UserCreate from './components/users/UsuarioCreate';
import { UsuarioList } from './components/users/UsuarioList';
import { ParceiroList } from './components/parceiros/ParceiroList';
import ParceiroCreate from './components/parceiros/ParceiroCreate';
import { dataProvider } from './dataProvider/dataProvider';
import UserEdit from './components/users/UsuarioEdit';
import ParceiroEdit from './components/parceiros/ParceiroEdit';
import TransacaoCreate from './components/transacoes/TransacaoCreate';
import { TransacaoList } from './components/transacoes/TransacaoList';
import TransacaoEdit from './components/transacoes/TransacaoEdit';
import { ArquivoList } from './components/arquivos/ArquivoList';
import ArquivoCreate from './components/arquivos/ArquivoCreate';
import LarTempCreate from './components/lar_temporarios/LarTempCreate';
import { LarTempList } from './components/lar_temporarios/LarTempList';
import ArquivoEdit from './components/arquivos/ArquivoEdit';
import LarTempEdit from './components/lar_temporarios/LarTempEdit';

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
        icon={PersonIcon}
        list={UsuarioList}
        edit={UserEdit}
        create={UserCreate}
      />
      <Resource
        options={{ label: "Parceiros" }}
        name="parceiros"
        icon={Groups2Icon}
        list={ParceiroList}
        edit={ParceiroEdit}
        create={ParceiroCreate}
      />
      <Resource
        options={{ label: "Transações" }}
        name="transacoes"
        icon={PaymentsRoundedIcon}
        list={TransacaoList}
        edit={TransacaoEdit}
        create={TransacaoCreate}
      />
      <Resource
        options={{ label: "Documentos" }}
        name="documentos"
        icon={DescriptionOutlinedIcon}
        list={ArquivoList}
        edit={ArquivoEdit}
        create={ArquivoCreate}
      />
      <Resource
        options={{ label: "Lares Temporários" }}
        name="lares-temporarios"
        icon={NightShelterOutlinedIcon}
        list={LarTempList}
        create={LarTempCreate}
        edit={LarTempEdit}
      />

    </Admin>
  )
}

export default App
