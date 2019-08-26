import React from 'react';
import { FunctionField, ImageField, ImageInput, RichTextField } from 'react-admin';
import RichTextInput from 'ra-input-rich-text';
import { HydraAdmin } from '@api-platform/admin';
import parseHydraDocumentation from '@api-platform/api-doc-parser/lib/hydra/parseHydraDocumentation';

const entrypoint = process.env.REACT_APP_API_ENTRYPOINT;

const myApiDocumentationParser = entrypoint => parseHydraDocumentation(entrypoint)
    .then( ({ api }) => {
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
        contentUrl.field = props => (
            <ImageField {...props} source="contentUrl"/>
        );
        contentUrl.input = props => (
            <ImageInput accept="image/*" key="contentUrl" multiple={false} source="contentUrl">
                <ImageField source="contentUrl"/>
            </ImageInput>
        );
        // contentUrl.normalizeData = value => {
        //     if (value && value.rawFile instanceof File) {
        //         const body = new FormData();
        //         body.append('file', value.rawFile);
        //
        //         return fetch(`${entrypoint}/images`, {body, method: 'POST', headers: {'Content-Type': 'multipart/form-data'}})
        //             .then(response => response.json())
        //             .then(image => {
        //                 console.log(image);
        //                 return image
        //             });
        //         r
        //     }
        // };

        return { api };
    })
;

export default (props) => <HydraAdmin apiDocumentationParser={myApiDocumentationParser} entrypoint={entrypoint}/>;