<?php

namespace App\Controller;

use App\Entity\Job;
use App\Repository\CompanyRepository;
use App\Repository\JobRepository;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Job")]
class JobController extends AbstractController
{
    #[Route(path: "/api/v1/jobs", methods: "POST")]
    #[OA\Post(description: "Create job.")]
    #[Security(name: "Bearer")]
    #[OA\RequestBody(
        description: "Json to create the job",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "title", type: "string", example: "Job title"),
                new OA\Property(property: "description", type: "string", example: "Description of the job"),
                new OA\Property(property: "requiredSkills", type: "string", example: "Skills for the job"),
                new OA\Property(property: "experience", type: "string", example: "Junior"),
                new OA\Property(property: "companyId", type: "string", example: "018733fb-d3d2-733a-9184-4a79ab743bd2")
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'Returns the ID of the job',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "statusCode", type: "int", example: 201),
                new OA\Property(property: "message", type: "string", example: "Job created"),
                new OA\Property(property: "data", type: "object")
            ]
        )
    )]
    #[OA\Response(
        response: 400,
        description: 'Invalid arguments',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "statusCode", type: "int", example: 400),
                new OA\Property(property: "message", type: "string", example: "Invalid arguments"),
                new OA\Property(property: "data", type: "object")
            ]
        )
    )]
    public function create(
        JobRepository $repository,
        CompanyRepository $companyRepository,
        Request $request,
        ValidatorInterface $validator
    ): Response
    {
        $jsonParams = json_decode($request->getContent(), true);

        $company = $companyRepository->find($jsonParams['companyId']);

        $job = new Job();
        $job->setTitle($jsonParams['title']);
        $job->setDescription($jsonParams['description']);
        $job->setRequiredSkills($jsonParams['requiredSkills']);
        $job->setCompany($company);
        $job->setExperience($jsonParams['experience']);
        $job->setCreatedAt(new \DateTimeImmutable());

        $violations = $validator->validate($job);

        if(count($violations) === 0){
            $repository->save($job, true);

            return $this->jsonResponse("Job created", [
                'id' => (string)$job->getId()
            ], 201);
        }

        $errorData = [];
        /** @var ConstraintViolationInterface $violation */
        foreach ($violations as $violation){
            $errorData[$violation->getPropertyPath()][] = $violation->getMessage();
        }

        return $this->jsonResponse("Invalid input", $errorData, 400);
    }

    #[Route(path: "/api/v1/jobs/{id}", methods: "GET")]
    #[OA\Get(description: "Return job by ID.")]
    #[Security(name: "Bearer")]
    public function findById(JobRepository $repository, string $id): Response
    {
        $job = $repository->find($id);

        if ($job === null) {
            return $this->jsonResponse("Job not found", ['id' => $id], 404);
        }

        return $this->jsonResponse("Job by ID", $job->toArray());
    }

    #[Route(path: "/api/v1/jobs", methods: "GET")]
    #[OA\Get(description: "Return the jobs depending on the filter.")]
    #[Security(name: "Bearer")]
    public function getJobs(Request $request, JobRepository $repository): Response
    {
        $qb = $repository->createQueryBuilder('j');

        $title = $request->query->get('title');
        if ($title !== null) {
            $qb->andWhere('j.title = :title')
                ->setParameter('title', $title);
        }

        $companyName = $request->query->get('company');
        if ($companyName !== null) {
            $qb->join('j.company', 'c')
                ->andWhere('c.name = :companyName')
                ->setParameter('companyName', $companyName);
        }

        $location = $request->query->get('location');
        if ($location !== null) {
            $qb->join('j.company', 'c')
                ->andWhere('c.location = :location')
                ->setParameter('location', $location);
        }

        $jobs = $qb->getQuery()->getResult();

        $response = [];
        foreach ($jobs as $job) {
            $response[] = $job->toArray();
        }

        return $this->jsonResponse("Filtered Jobs", $response);
    }

    #[Route(path: "/api/v1/jobs/{id}", methods: "PUT")]
    #[OA\Put(description: "Update the job by ID.")]
    #[Security(name: "Bearer")]
    #[OA\RequestBody(
        description: "Json to update the job",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "title", type: "string", example: "Job title"),
                new OA\Property(property: "description", type: "string", example: "Description of the job"),
                new OA\Property(property: "requiredSkills", type: "string", example: "Skills for the job"),
                new OA\Property(property: "experience", type: "string", example: "Junior")
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns the properties of the job',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "statusCode", type: "int", example: 200),
                new OA\Property(property: "message", type: "string", example: "Job updated"),
                new OA\Property(property: "data", type: "object")
            ]
        )
    )]
    #[OA\Response(
        response: 400,
        description: 'Invalid arguments',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "statusCode", type: "int", example: 400),
                new OA\Property(property: "message", type: "string", example: "Invalid arguments"),
                new OA\Property(property: "data", type: "object")
            ]
        )
    )]
    public function update(
        JobRepository $repository,
        Request $request,
        string $id,
        ValidatorInterface $validator
    ): Response
    {
        $job = $repository->find($id);

        if ($job === null) {
            return $this->jsonResponse("Job not found", ['id' => $id], 404);
        }

        $jsonParams = json_decode($request->getContent(), true);

        $job->setTitle($jsonParams['title'] ?? $job->getTitle());
        $job->setDescription($jsonParams['description'] ?? $job->getDescription());
        $job->setRequiredSkills($jsonParams['requiredSkills'] ?? $job->getRequiredSkills());
        $job->setCompany($jsonParams['companyId'] ?? $job->getCompany());
        $job->setExperience($jsonParams['experience'] ?? $job->getExperience());
        $job->setUpdatedAt(new \DateTimeImmutable());


        $violations = $validator->validate($job);

        if(count($violations) === 0){
            $repository->save($job, true);

            return $this->jsonResponse("Job updated", [
                $job->toArray()
            ]);
        }

        $errorData = [];
        /** @var ConstraintViolationInterface $violation */
        foreach ($violations as $violation){
            $errorData[$violation->getPropertyPath()][] = $violation->getMessage();
        }

        return $this->jsonResponse("Invalid input", $errorData, 400);
    }

    #[Route(path: "/api/v1/jobs/{id}", methods: "DELETE")]
    #[OA\Delete(description: "Delete the job by ID")]
    #[Security(name: "Bearer")]
    public function delete(JobRepository $repository, string $id): Response
    {
        $job = $repository->find($id);

        if ($job === null) {
            return $this->jsonResponse("Job not found", ['id' => $id], 404);
        }

        $repository->remove($job, true);

        return $this->jsonResponse("Job deleted", [
            $job->toArray()
        ]);
    }

    private function jsonResponse(string $message, array $data, int $statusCode = 200): JsonResponse
    {
        return $this->json([
            "statusCode" => $statusCode,
            "message" => $message,
            "data" => $data
        ], $statusCode);
    }
}