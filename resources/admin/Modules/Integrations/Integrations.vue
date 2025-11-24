<template>
    <div class="fct-integration-container fct-layout-width">
        <CardContainer>
            <CardBody>
                <div class="text-center py-12">
                    <h2 class="fct-card-header-title mb-3">Need help or customisation?</h2>
                    <p class="card-header-text mt-0 mb-6">
                        We’re here to support your business and tailor Webmakerr to your needs.
                    </p>
                    <el-button type="primary" size="large" @click="goToContact">
                        Contact Now
                    </el-button>
                </div>
            </CardBody>
        </CardContainer>
    </div>
</template>

<script type="text/babel">
import {Container as CardContainer, Body as CardBody} from '@/Bits/Components/Card/Card.js';

export default {
    name: "Integrations",
    components: {
        CardBody,
        CardContainer
    },
    methods: {
        goToContact() {
            window.open('https://webmakerr.com/contact-us', '_blank');
        }
    }
};
</script>
