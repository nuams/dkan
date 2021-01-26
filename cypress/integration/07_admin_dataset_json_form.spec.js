context('Admin dataset json form', () => {
    let baseurl = Cypress.config().baseUrl;
    beforeEach(() => {
        cy.drupalLogin('testeditor', 'testeditor')
    })

    it('The dataset form has the correct required fields.', () => {
        cy.visit(baseurl + "/node/add/data")
        cy.get('#edit-field-json-metadata-0-value-title').should('have.attr', 'required', 'required')
        cy.get('#edit-field-json-metadata-0-value-description').should('have.attr', 'required', 'required')
        cy.get('#edit-field-json-metadata-0-value-accesslevel').should('have.attr', 'required', 'required')
        cy.get('#edit-field-json-metadata-0-value-modified').should('have.attr', 'required', 'required')
        cy.get('#edit-field-json-metadata-0-value-publisher-publisher-name').should('have.attr', 'required', 'required')
        cy.get('#edit-field-json-metadata-0-value-contactpoint-contactpoint-fn').should('have.attr', 'required', 'required')
        cy.get('#edit-field-json-metadata-0-value-contactpoint-contactpoint-hasemail').should('have.attr', 'required', 'required')
    })

    it('User can create a dataset with the json form UI.', () => {
        cy.visit(baseurl + "/node/add/data")
        cy.wait(2000)
        cy.get('#edit-field-json-metadata-0-value-title').type('DKANTEST dataset title', { force:true } )
        cy.get('#edit-field-json-metadata-0-value-description').type('DKANTEST dataset description.', { force:true } )
        cy.get('#edit-field-json-metadata-0-value-accesslevel').select('public', { force:true } )
        cy.get('#edit-field-json-metadata-0-value-modified').type('2020-02-02', { force:true } )
        cy.get('#edit-field-json-metadata-0-value-publisher-publisher-name').type('DKANTEST Publisher', { force:true } )
        cy.get('#edit-field-json-metadata-0-value-contactpoint-contactpoint-fn').type('DKANTEST Contact Name', { force:true } )
        cy.get('#edit-field-json-metadata-0-value-contactpoint-contactpoint-hasemail').type('mailto:dkantest@test.com', { force:true } )
        cy.get('#edit-field-json-metadata-0-value-keyword-keyword-0').type('open data', { force: true })
        cy.get('#edit-submit').click({ force:true })
        cy.get('.messages--status').should('contain','has been created')
    })

    it('Admin user can edit a dataset with the json form UI.', () => {
        cy.visit(baseurl + "/admin/content/datasets")
        cy.get('#edit-title').type('DKANTEST dataset title', { force:true } )
        cy.get('#edit-submit-dkan-dataset-content').click({ force:true })
        cy.get('tbody > tr:first-of-type > .views-field-nothing > a').click({ force:true })
        cy.wait(2000)
        cy.get('#edit-field-json-metadata-0-value-title').should('have.value','DKANTEST dataset title')
        cy.get('#edit-field-json-metadata-0-value-title').type('NEW dkantest dataset title',{ force:true })
        cy.get('#edit-field-json-metadata-0-value-accrualperiodicity').select('Annual', { force:true })
        cy.get('#edit-field-json-metadata-0-value-keyword-actions-actions-add').click({ force:true })
        cy.get('.js-form-item-field-json-metadata-0-value-keyword-keyword-1 > input').type('testing', { force:true })
        cy.get('#edit-field-json-metadata-0-value-distribution-distribution-0-distribution-title').type('DKANTEST distribution title text', { force:true })
        cy.get('#edit-field-json-metadata-0-value-distribution-distribution-0-distribution-description').type('DKANTEST distribution description text', { force:true })
        cy.get('#edit-field-json-metadata-0-value-distribution-distribution-0-distribution-format').type('csv', { force:true })
        cy.get('#edit-submit').click({ force:true })
        cy.get('.messages--status').should('contain','has been updated')
    })

    it('Admin user can delete a dataset (cleanup)', () => {
        cy.visit(baseurl + "/admin/content/datasets")
        cy.wait(2000)
        cy.get('#edit-node-bulk-form-0').check({ force:true })
        cy.get('#edit-submit--2').click({ force:true })
        cy.get('input[value="Delete"]').click({ force:true })
        cy.get('.messages').should('contain','Deleted 1 content item.')
    })

})
