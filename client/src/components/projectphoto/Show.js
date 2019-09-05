import React, { Component } from 'react';
import { connect } from 'react-redux';
import { Link, Redirect } from 'react-router-dom';
import PropTypes from 'prop-types';
import { retrieve, reset } from '../../actions/projectphoto/show';

class Show extends Component {
  static propTypes = {
    retrieved: PropTypes.object,
    loading: PropTypes.bool.isRequired,
    error: PropTypes.string,
    eventSource: PropTypes.instanceOf(EventSource),
    retrieve: PropTypes.func.isRequired,
    reset: PropTypes.func.isRequired,
  };

  componentDidMount() {
    this.props.retrieve(decodeURIComponent(this.props.match.params.id));
  }

  componentWillUnmount() {
    this.props.reset(this.props.eventSource);
  }

  del = () => {
    if (window.confirm('Are you sure you want to delete this item?'))
      this.props.del(this.props.retrieved);
  };

  render() {
    const item = this.props.retrieved;

    return (
      <div>
        <h1>Show {item && item['@id']}</h1>

        {this.props.loading && (
          <div className="alert alert-info" role="status">
            Loading...
          </div>
        )}
        {this.props.error && (
          <div className="alert alert-danger" role="alert">
            <span className="fa fa-exclamation-triangle" aria-hidden="true" />{' '}
            {this.props.error}
          </div>
        )}

        {item && (
          <table className="table table-responsive table-striped table-hover">
            <thead>
              <tr>
                <th>Field</th>
                <th>Value</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <th scope="row">name</th>
                <td>{item['name']}</td>
              </tr>
              <tr>
                <th scope="row">shortDescription</th>
                <td>{item['shortDescription']}</td>
              </tr>
              <tr>
                <th scope="row">photo</th>
                <td><img src={item['url']} alt={item['name']}/></td>
              </tr>
              <tr>
                <th scope="row">project</th>
                <td>{this.renderLinks('projects', item['project'])}</td>
              </tr>
              <tr>
                <th scope="row">createdAt</th>
                <td>{item['createdAt']}</td>
              </tr>
              <tr>
                <th scope="row">updatedAt</th>
                <td>{item['updatedAt']}</td>
              </tr>
            </tbody>
          </table>
        )}
      </div>
    );
  }

  renderLinks = (type, items) => {
    if (Array.isArray(items)) {
      return items.map((item, i) => (
        <div key={i}>{this.renderLinks(type, item)}</div>
      ));
    }

    return (
      <Link to={`/${type}/show/${encodeURIComponent(items)}`}>{items}</Link>
    );
  };
}

const mapStateToProps = state => ({
  retrieved: state.projectphoto.show.retrieved,
  error: state.projectphoto.show.error,
  loading: state.projectphoto.show.loading,
  eventSource: state.projectphoto.show.eventSource,
});

const mapDispatchToProps = dispatch => ({
  retrieve: id => dispatch(retrieve(id)),
  reset: eventSource => dispatch(reset(eventSource))
});

export default connect(
  mapStateToProps,
  mapDispatchToProps
)(Show);
