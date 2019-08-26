import React from 'react';
import { FunctionField, ImageField, ImageInput, RichTextField } from 'react-admin';
import { HydraAdmin } from '@api-platform/admin';
import parseHydraDocumentation from '@api-platform/api-doc-parser/lib/hydra/parseHydraDocumentation';

const entrypoint = process.env.REACT_APP_API_ENTRYPOINT;

const myApiDocumentationParser = entrypoint => parseHydraDocumentation(entrypoint)
    .then( ({ api }) => {
        api.resources.map(resource => {
            resource.fields.map(field => {
                console.log(field);
                if ('contentUrl' === field.name) {
                    field.denormalizeData = value => ({
                        src: value
                    });
                    field.field = props => (
                        <ImageField {...props} source={`${field.name}.src`} />
                    );
                }

                return field;
            });

            return resource;
        });

        return { api };
    })
;

export default (props) => <HydraAdmin apiDocumentationParser={myApiDocumentationParser} entrypoint={entrypoint}/>;