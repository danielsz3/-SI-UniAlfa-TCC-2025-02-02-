import jsonServerProvider from 'ra-data-json-server'
import type { DataProvider } from 'react-admin'

const dataProvider: DataProvider = jsonServerProvider('http://127.0.0.1:8000/api')

export default dataProvider