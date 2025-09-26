import React from 'react';
import {
    Layout as LayoutRa, 
    AppBar,
    type AppBarProps,
    type LayoutProps,
    type MenuProps,
    Menu
} from 'react-admin';
import MenuUsuario from '../components/users/MenuUsuario'; 
import WebhookOutlinedIcon from '@mui/icons-material/WebhookOutlined';

const MinhaAppBar: React.FC<AppBarProps> = (props) => (
    <AppBar {...props} userMenu={<MenuUsuario />} />
);

const CustomMenu: React.FC<MenuProps> = (props) => (
    <Menu {...props}>
        <Menu.ResourceItems />
        <Menu.Item to="/integracoes" primaryText="Integrações" leftIcon={<WebhookOutlinedIcon />} />
    </Menu>
);

const Layout: React.FC<LayoutProps> = (props) => (
    <LayoutRa {...props} appBar={MinhaAppBar} menu={CustomMenu} />
);

export default Layout;