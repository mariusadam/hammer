import React from 'react';
import { Route } from 'react-router-dom';
import { List, Create, Update, Show } from '../components/projectphoto/';

export default [
  <Route path="/project_photos/show/:id" component={Show} exact key="show" />,
];
