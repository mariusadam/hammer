import React from 'react';
import { Route } from 'react-router-dom';
import { List, Create, Update, Show } from '../components/image/';

export default [
  <Route path="/images/show/:id" component={Show} exact key="show" />,
];
