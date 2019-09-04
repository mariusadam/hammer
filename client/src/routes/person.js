import React from 'react';
import { Route } from 'react-router-dom';
import { List, Show } from '../components/person/';

export default [
  <Route path="/people/show/:id" component={Show} exact key="show" />,
  <Route path="/people/" component={List} exact strict key="list" />,
  <Route path="/people/:page" component={List} exact strict key="page" />
];
