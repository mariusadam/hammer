import React from 'react';
import {FunctionField, ImageField, ImageInput, RichTextField} from 'react-admin';
import RichTextInput from 'ra-input-rich-text';
import {HydraAdmin} from '@api-platform/admin';
import parseHydraDocumentation from '@api-platform/api-doc-parser/lib/hydra/parseHydraDocumentation';

const entrypoint = process.env.REACT_APP_API_ENTRYPOINT;

const toBase64 = file => new Promise((resolve, reject) => {
  const reader = new FileReader();
  reader.readAsDataURL(file);
  reader.onload = () => resolve(reader.result);
  reader.onerror = error => reject(error);
});

const myApiDocumentationParser = entrypoint => parseHydraDocumentation(entrypoint)
  .then(({api}) => {
    const projects = api.resources.find(({name}) => 'projects' === name);
    const description = projects.fields.find(f => 'description' === f.name);

    description.field = props => (
      <RichTextField {...props} source="description"/>
    );
    description.input = props => (
      <RichTextInput {...props} source="description"/>
    );
    description.input.defaultProps = {
      addField: true,
      addLabel: true
    };

    const images = api.resources.find(({name}) => 'images' === name);
    const contentUrl = images.fields.find(f => 'contentUrl' === f.name);
    const imageFile = images.fields.find(f => 'imageFile' === f.name);
    contentUrl.field = props => (
      <ImageField {...props} source="contentUrl"/>
    );

    imageFile.input = props => (
      <ImageInput accept="image/*" key="imageFasdile" multiple={false} source="imageFile">
        <ImageField source="imageFileaaassadad"/>
      </ImageInput>
    );

    imageFile.normalizeData = value => {
      if (value && value.rawFile instanceof File) {
        return toBase64(value.rawFile);
      }

      return null;
    };

    return {api};
  })
;

export default (props) => <HydraAdmin apiDocumentationParser={myApiDocumentationParser} entrypoint={entrypoint}/>;
