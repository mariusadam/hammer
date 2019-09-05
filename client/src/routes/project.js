import React from 'react';
import { Route } from 'react-router-dom';
import { List, Create, Update, Show } from '../components/project/';

export default [
  <Route path="/projects/show/:id" component={Show} exact key="show" />,
  <Route path="/projects/" component={List} exact strict key="list" />,
  <Route path="/projects/:page" component={List} exact strict key="page" />
];
