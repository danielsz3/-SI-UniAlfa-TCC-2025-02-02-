import React from 'react';
import {
    Layout as LayoutRa, 
    AppBar,
    type AppBarProps,
    type LayoutProps
} from 'react-admin';
import MenuUsuario from '../components/users/MenuUsuario'; 

const MinhaAppBar: React.FC<AppBarProps> = (props) => (
    <AppBar {...props} userMenu={<MenuUsuario />} />
);

const Layout: React.FC<LayoutProps> = (props) => (
    <LayoutRa {...props} appBar={MinhaAppBar} />
);

export default Layout;