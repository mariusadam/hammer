import React from 'react';
import { Route } from 'react-router-dom';
import { List, Create, Update, Show } from '../components/building/';

export default [
  <Route path="/buildings/create" component={Create} exact key="create" />,
  <Route path="/buildings/edit/:id" component={Update} exact key="update" />,
  <Route path="/buildings/show/:id" component={Show} exact key="show" />,
  <Route path="/buildings/" component={List} exact strict key="list" />,
  <Route path="/buildings/:page" component={List} exact strict key="page" />
];
